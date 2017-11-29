<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchDSL\SearchEndpoint;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Search sort dsl endpoint.
 */
class CollapseEndpoint extends AbstractSearchEndpoint
{
    /**
     * Endpoint name
     */
    const NAME = 'collapse';

    private $field;

    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        return ['field' => $this->field];

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function addField($field)
    {
        $this->field = $field;
    }

    /**
     * @return BuilderInterface
     */
    public function getCollapse()
    {
        return ['field' => $this->field];
    }
}
