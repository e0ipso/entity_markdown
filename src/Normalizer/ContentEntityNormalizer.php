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
  public function normalize($entity, $format = NULL, array $context = []) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    // Add a heading with the label information with a default value.
    $normalized['label'] = [
      'prefix' => '# ',
      'value' => $entity->label() ?: $this->t('- No label found -'),
    ];

    // Add some interesting metadata.
    $normalized = array_merge($normalized, [
      'entity_type' => ['prefix' => '## ', 'value' => $entity->getEntityTypeId()],
      'bundle' => ['prefix' => '## ', 'value' => $entity->bundle() ?: $this->t('- Entity without a bundle -')],
      'language' => ['prefix' => '## ', 'value' => $entity->language()->getName()],
    ]);

    // Add the information about the fields. This code will return an array with
    // the information for each field in the entity.
    $normalized_fields = [];
    foreach ($entity->getFields(TRUE) as $field_item_list) {
      // Defer the field normalization to other individual normalizers.
      $normalized_field_item = $this->serializer->normalize($field_item_list, $format, $context);
      $normalized_fields[] = $normalized_field_item['name'];
      $normalized_fields[] = $normalized_field_item['content'];
    }

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

}
