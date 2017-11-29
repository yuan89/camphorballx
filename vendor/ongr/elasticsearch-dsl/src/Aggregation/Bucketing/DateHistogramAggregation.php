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

/**
 * Class representing Histogram aggregation.
 *
 * @link https://goo.gl/hGCdDd
 */
class DateHistogramAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @var string
     */
    protected $interval;
    protected $min;
    protected $max;

    /**
     * Inner aggregations container init.
     *
     * @param string $name
     * @param string $field
     * @param string $interval
     */
    public function __construct($name, $field = null, $interval = null, $min = null, $max = null)
    {
        parent::__construct($name);

        $this->setField($field);
        $this->setInterval($interval);

        if ($min) {
            $this->setMin($min);
        }

        if ($max) {
            $this->setMax($max);
        }
    }

    public function getMin()
    {
        return $this->min;
    }

    public function setMin($min)
    {
        $this->min = $min;
    }

    public function getMax()
    {
        return $this->max;
    }

    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param string $interval
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'date_histogram';
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        if (!$this->getField() || !$this->getInterval()) {
            throw new \LogicException('Date histogram aggregation must have field and interval set.');
        }

        $out = [
            'field' => $this->getField(),
            'interval' => $this->getInterval(),
            'min_doc_count' => 0,
        ];

        if ($this->getMin() && $this->getMax()) {
            $out['extended_bounds'] = ['min' => $this->getMin(),'max' => $this->getMax()];
        }

        return $out;
    }
}
