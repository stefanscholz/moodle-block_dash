<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dash\data_grid\data;

class data_collection implements data_collection_interface, \ArrayAccess
{
    /**
     * @var field_interface[]
     */
    private $data = [];

    /**
     * @var array type => [array of collections]
     */
    private $children = [];

    /**
     * Get all fields in this data collection.
     *
     * @return field_interface[]
     */
    public function get_data()
    {
        return array_values($this->data);
    }

    /**
     * Add data to data collection.
     *
     * @param field_interface $field
     */
    public function add_data(field_interface $field)
    {
        $this->data[$field->get_name()] = $field;
    }

    /**
     * Add raw data to collection.
     *
     * @param array $data Associative array of data
     */
    public function add_data_associative($data)
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = new field($key, $value);
        }
    }

    /**
     * Get child data collections.
     *
     * @param string $type Name of collection type to return. Null returns all.
     * @return data_collection_interface[]
     */
    public function get_child_collections($type = null)
    {
        if ($type) {
            if (isset($this->children[$type])) {
                return $this->children[$type];
            }
        } else {
            $children = [];

            foreach ($this->children as $set) {
                $children = array_merge($set, $children);
            }

            return $children;
        }

        return [];
    }

    /**
     * Add a child data collection.
     *
     * @param string $type Name of collection type.
     * @param data_collection_interface $collection
     */
    public function add_child_collection($type, data_collection_interface $collection)
    {
        if (!isset($this->children[$type])) {
            $this->children[$type] = [];
        }
        $this->children[$type][] = $collection;
    }

    /**
     * Check if this collection contains any child collection of data.
     *
     * @return bool
     */
    public function has_child_collections()
    {
        return count($this->children) > 0;
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        if ($offset == 'data') {
            return true;
        }
        return isset($this->data[$offset]) || isset($this->children[$offset]);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if ($offset == 'data') {
            return $this->get_data();
        }

        if (isset($this->data[$offset])) {
            return $this->data[$offset]->get_value();
        } else {
            return $this->children[$offset];
        }
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        throw new \coding_exception('Setting data not supported with array access.');
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        throw new \coding_exception('Unsetting data not supported with array access.');
    }
}