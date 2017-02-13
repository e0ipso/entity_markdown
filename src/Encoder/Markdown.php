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
  public function encode($data, $format, array $context = []) {
    $lines = [];
    foreach ($data as $item) {
      $line = isset($item['value']) ? $item['value'] : '';
      $line = isset($item['prefix']) ? $item['prefix'] . $line : $line;
      $line = isset($item['suffix']) ? $item['suffix'] . $line : $line;
      $lines[] = $line;
    }

    return implode("\n", $lines);
  }

}
