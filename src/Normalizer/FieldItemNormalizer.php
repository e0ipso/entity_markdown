<?php


namespace Drupal\entity_markdown\Normalizer;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

class FieldItemNormalizer extends SerializerAwareNormalizer implements NormalizerInterface {

  const FORMAT = 'markdown';

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = []) {
    /** @var \Drupal\Core\Field\FieldItemInterface $field_item */
    $data_definition = $field_item->getDataDefinition();
    $property_definitions = $data_definition->getPropertyDefinitions();
    $num_properties = count(array_filter(
      $property_definitions,
      function (DataDefinitionInterface $property_definition) {
        return !$property_definition->isComputed();
      }
    ));
    // Normalize the array of field item values. Each one can contain multiple
    // properties, we we'll need to normalize that as well.
    if ($num_properties == 1) {
      // If there is only one property, just output that one.
      // We want to output the main property.
      $main_property_name = $field_item->getDataDefinition()
        ->getMainPropertyName();
      $main_property = $field_item->get($main_property_name);
      return $this->serializer->normalize($main_property) ?: '';
    }

    $output = "\n";
    foreach ($field_item as $property_name => $property) {
      // This will generate a nested list (in case the field is multivalue) with
      // the property in bold followed by a ':' and the value.
      $item_value = $this->serializer->normalize($property, $format, $context);
      $output .= "  * **$property_name**: $item_value\n";
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    // Only consider this normalizer if we are trying to normalize a field item
    // list into the 'markdown' format.
    return $format === static::FORMAT && $data instanceof FieldItemInterface;
  }


}
