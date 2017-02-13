<?php


namespace Drupal\entity_markdown\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

class ContentEntityNormalizer extends SerializerAwareNormalizer implements NormalizerInterface {

  use StringTranslationTrait;

  const FORMAT = 'markdown';

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = array()) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $object */
    // Add a heading with the label information with a default value.
    $normalized['label'] = [
      'prefix' => '# ',
      'value' => $object->label() ?: $this->t('- No label found -'),
    ];

    // Add some interesting metadata.
    $normalized = array_merge($normalized, [
      'entity_type' => ['prefix' => '## ', 'value' => $object->getEntityTypeId()],
      'bundle' => ['prefix' => '## ', 'value' => $object->bundle() ?: $this->t('- Entity without a bundle -')],
      'language' => ['prefix' => '## ', 'value' => $object->language()->getName()],
    ]);

    // Add the information about the fields. This code will return an array with
    // the information for each field in the entity.
    $normalized_fields = array_map(function (FieldItemListInterface $field_item_list) {
      // Now transform the field into a string version of it. We want to output
      // the name of the field and the value.
      $normalized_field = [
        'name' => ['prefix' => '### ', 'value' => $field_item_list->getName()]
      ];
      // If the field can hold multiple values we want them as a list. If not,
      // as a plain value. For that we need to check how the field is defined to
      // see the cardinality value.
      $cardinality = $field_item_list
        ->getFieldDefinition()
        ->getFieldStorageDefinition()
        ->getCardinality();

      // We also want to check if the field has only one property (typically
      // called 'value'). If that is the case, we will just return the content,
      // and not bother with the property name.
      $field_values = $field_item_list->getValue();
      $property_definitions = $field_item_list
        ->getItemDefinition()
        ->getPropertyDefinitions();
      $num_properties = count($property_definitions);

      // Normalize the array of field item values. Each one can contain multiple
      // properties, we we'll need to normalize that as well.
      $field_item_values = array_map(function ($field_item_value) use ($num_properties) {
        return $this->normalizeFieldItemValue($field_item_value, $num_properties == 1);
      }, $field_values);
      // If this is a single field, just consider the first item.
      if ($cardinality == 1) {
        $normalized_field['content'] = ['value' => $field_item_values[0]];
      }
      else {
        $content = array_reduce(
          $field_item_values,
          function ($carry, $field_item_value) {
            // Prefix each value with a '* ' and suffix with a \n.
            return $carry . "* $field_item_value\n";
          },
          "\n"
        );
        $normalized_field['content'] = ['value' => $content];
      }

      return $normalized_field;
    }, $object->getFields(TRUE));

    // Append the field information to the normalized data array.
    $normalized['fields'] = ['prefix' => '## ', 'value' => $this->t('Fields')];
    $normalized = array_merge($normalized, $normalized_fields);

    return $normalized;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    // Only consider this normalizer if we are trying to normalize a content
    // entity into the 'markdown' format.
    return $format === static::FORMAT && $data instanceof ContentEntityInterface;
  }

  /**
   * Normalizes a field item value.
   *
   * @param array $item_values
   *   The array of values keyed by property name.
   * @param bool $is_single_property
   *   TRUE if this field only accepts a single value.
   *
   * @return string
   *   The normalized value.
   */
  protected function normalizeFieldItemValue($item_values, $is_single_property) {
    // If there is only one property, just output that one.
    if ($is_single_property) {
      return array_shift($item_values) ?: '';
    }

    $output = '';
    foreach ($item_values as $property => $item_value) {
      // This will generate a nested list (in case the field is multivalue) with
      // the property in bold followed by a ':' and the value.
      $output .= "    * **$property**: $item_value";
    }

    return $output;
  }

}
