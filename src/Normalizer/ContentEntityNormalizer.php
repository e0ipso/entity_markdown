<?php


namespace Drupal\entity_markdown\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\scalar;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

class ContentEntityNormalizer extends SerializerAwareNormalizer implements NormalizerInterface {

  const FORMAT = 'markdown';

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = array()) {
    throw new \RuntimeException('Normalization is not yet implemented.');
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
