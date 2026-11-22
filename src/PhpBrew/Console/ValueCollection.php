<?php

namespace PhpBrew\Console;

class ValueCollection
{
    /**
     * @var array<string, array>
     */
    protected $groups = array();

    public function group($id, $label, array $values)
    {
        $this->groups[$id] = array(
            'label' => $label,
            'values' => $values,
        );
        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }
}
