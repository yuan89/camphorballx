<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchDSL\Collapse;

use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\ParametersTrait;

/**
 * Holds all the values required for basic sorting.
 */
class FieldCollapse implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    private $field;

    /**
     * @param string $field  Field name.
     * @param string $order  Order direction.
     * @param array  $params Params that can be set to field sort.
     */
    public function __construct($field)
    {
        $this->field = $field;
    }


    /**
     * Returns element type.
     *
     * @return string
     */
    public function getType()
    {
        return 'collapse';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $output = [
            $this->field => !$this->getParameters() ? new \stdClass() : $this->getParameters(),
        ];

        return $output;
    }
}
