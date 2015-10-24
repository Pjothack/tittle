<?php

namespace Tittle\DatabaseModels;

use Tittle\DatabaseModels\Tag as TagModel;

class Category extends \Illuminate\Database\Eloquent\Model
{
    public $timestamps = false;

    public static function byTags($tags)
    {
        $known_tags = TagModel::all();

        foreach ($tags as $tag) {
            $possible_matching_known_tag = TagModel::where('name', $tag)->first();

            if ($possible_matching_known_tag) {
                return $possible_matching_known_tag->category;
            }
        }

        // Couldn't match anything in the about foreach
        return null;
    }

    public static function idByName($name)
    {
        return self::where('name', $name)->first();
    }
}
