<?php

namespace WC\Utilities;

final class PregUtil
{
    private function __construct(){}

    public static function getMatches(string $pattern, string $subject): array {
        try {
            $matches = array();
            preg_match_all($pattern, $subject, $matches, PREG_SET_ORDER);
            if (self::matchesFound($matches)) {
                return $matches;
            }
        }
        catch (\Exception $e) {}
        return array();
    }

    public static function matchesFound(array &$matches): bool {
        if (sizeof($matches) > 1 && is_array($matches[1]) && isset($matches[1][0]) && strlen($matches[1][0])) {
            return true;
        }
        if (sizeof($matches) > 0 && is_array($matches[0]) && sizeof($matches[0]) > 1 &&
            isset($matches[0][0]) && isset($matches[0][1]) && strlen($matches[0][0]) && strlen($matches[0][1])) {
            return true;
        }
        return false;
    }

    public static function getImages(string $htmlString, bool $getAttrs = FALSE)
    {
        $postImages = array();

        // Get all images
        preg_match_all("<img (.+)>", $htmlString, $image_matches, PREG_SET_ORDER);

        // Loop the images and add the raw img html tag to $postImages
        foreach ($image_matches as $image_match)
        {
            $image = ['html'=>$image_match[0]];

            // If attributes have been requested get them and add them to the $image
            if ($getAttrs)
            {
                preg_match_all('/\s+?(.+)="([^"]*)"/U', $image_match[0], $image_attr_matches, PREG_SET_ORDER);

                foreach ($image_attr_matches as $image_attr)
                {
                    $image['attr'][$image_attr[1]] = $image_attr[2];
                }
            }

            $postImages[] = $image;
        }

        return $postImages;
    }
}