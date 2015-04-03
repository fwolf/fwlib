<?php
namespace Fwlib\Html\ListView;

/**
 * RendererInterface
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface RendererInterface
{
    /**
     * Get final output
     *
     * @return  string
     */
    public function getHtml();

    /**
     * Set single config value
     *
     * @param   string       $key
     * @param   mixed        $val
     * @return  static
     */
    public function setConfig($key, $val);

    /**
     * Batch set config values
     *
     * @param   array   $configs
     * @return  static
     */
    public function setConfigs(array $configs);

    /**
     * Setter of list dto
     *
     * @param   ListDto $listDto
     * @return  static
     */
    public function setListDto(ListDto $listDto);

    /**
     * Setter of content after list
     *
     * @param   string  $postContent
     * @return  static
     */
    public function setPostContent($postContent);

    /**
     * Setter of content before list
     *
     * @param   string  $preContent
     * @return  static
     */
    public function setPreContent($preContent);

    /**
     * Setter of row adjuster
     *
     * $adjuster takes row array as parameter, return adjusted row array.
     *
     * @param   callable    $adjuster
     * @return  static
     */
    public function setRowAdjuster(callable $adjuster);
}
