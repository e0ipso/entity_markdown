<?php

namespace Drupal\entity_markdown\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

class Markdown implements EncoderInterface {

  /**
   * The formats that this Encoder supports.
   *
   * @var array
   */
  protected static $format = 'markdown';

  /**
   * {@inheritdoc}
   */
  public function supportsEncoding($format) {
    return $format == static::$format;
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data, $format, array $context = array()) {
    throw new \RuntimeException('Encoding is not yet implemented.');
  }

}
