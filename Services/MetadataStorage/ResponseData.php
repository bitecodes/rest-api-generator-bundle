<?php

namespace Fludio\RestApiGeneratorBundle\Services\MetadataStorage;

class ResponseData
{
    protected $data = [
        'links' => [],
        'meta' => [],
    ];

    public function all()
    {
        return $this->data;
    }

    public function addLink($name, $link)
    {
        $this->data['links'][$name] = $link;
    }

    public function getLinks()
    {
        return $this->data['links'];
    }

    public function addMeta($name, $data)
    {
        $this->data['meta'][$name] = $data;
    }

    public function getMeta($name)
    {
        return $this->data['meta'];
    }
}
