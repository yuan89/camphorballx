<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchDSL\Aggregation\Bucketing;

use ONGR\ElasticsearchDSL\Aggregation\AbstractAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Type\BucketingTrait;
use ONGR\ElasticsearchDSL\ScriptAwareTrait;

/**
 * Class representing TermsAggregation.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html
 */
class TermsAggregation extends AbstractAggregation
{
    use BucketingTrait;
    use ScriptAwareTrait;

    private $order;
    /**
     * Inner aggregations container init.
     *
     * @param string $name
     * @param string $field
     * @param string $script
     */
    public function __construct($name, $field = null, $size = 1000000, $order = null, $script = null)
    {
        parent::__construct($name);

        $this->setField($field);
        $this->setSize($size);
        if ($script) {
            $this->setScript($script);
        }
        $this->setOrder($order);
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'terms';
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $data = array_filter(
            [
                'field' => $this->getField(),
                'size' => $this->getSize(),
                'script' => $this->getScript(),
                'order' => $this->getOrder(),
            ]
        );

        return $data;
    }
}
