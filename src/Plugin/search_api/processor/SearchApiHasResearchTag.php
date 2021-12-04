<?php

namespace Drupal\search_api_has_research_tag\Plugin\search_api\processor;

use Drupal\search_api\Plugin\PluginFormTrait;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Excludes entities marked as 'excluded' from being indexes.
 *
 * @SearchApiProcessor(
 *   id = "search_api_has_research_tag",
 *   label = @Translation("Search API Has Research Tag - Custom Processor"),
 *   description = @Translation("Requires presence of a Research tag to add nodes from News bundle to search index."),
 *   stages = {
 *     "alter_items" = -50
 *   }
 * )
 */
class SearchApiHasResearchTag extends ProcessorPluginBase {

  use PluginFormTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    return $processor;
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items) {

    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($items as $item_id => $item) {
      $object = $item->getOriginalObject()->getValue();
      $bundle = $object->bundle();

      // skip unless we have a news item with the topics taxref field

      if ($bundle == 'yse_detail_news' && $object->hasField('field_yse_topics_taxref')) {
        $nodescore = 0;
        foreach ($object->field_yse_topics_taxref->referencedEntities() as $term) {
          $termname = $term->getName();
          if ($termname == 'Research'){
            $nodescore++;
          }
        }
        if ($nodescore == 0){
          unset($items[$item_id]);
          continue;
        }
      }
    }
  }
}
