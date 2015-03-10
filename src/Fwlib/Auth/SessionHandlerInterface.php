<?php
namespace Fwlib\Auth;

/**
 * Session handler
 *
 * Provide variant access of session storage.
 *
 * @see session_set_save_handler()  As reference but not implement that.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface SessionHandlerInterface
{
    /**
     * Clear data but keep session opened
     *
     * @return  static
     */
    public function clear();


    /**
     * Destroy and close session
     *
     * @return  static
     */
    public function destroy();


    /**
     * Getter of session content
     *
     * @param   string|int  $name
     * @return  string|int
     */
    public function get($name);


    /**
     * Getter of session id
     *
     * @return  string|int
     */
    public function getId();


    /**
     * Is session opened ?
     *
     * @return  bool
     */
    public function isOpened();


    /**
     * Create or load then start a session, as constructor of session storage
     *
     * For given valid id of persisted session, should load it.
     *
     * @return  static
     */
    public function open();


    /**
     * Regenerate session id
     *
     * @see http://stackoverflow.com/a/22965580/1759745
     *
     * @return  static
     */
    public function regenerateId();


    /**
     * Save session to storage
     *
     * @return  static
     */
    public function save();


    /**
     * Setter of session content
     *
     * @param   string|int  $name
     * @param   mixed       $value
     * @return  static
     */
    public function set($name, $value);


    /**
     * Setter of session id, used to load exists session
     *
     * @param   string|int  $id
     * @return  static
     */
    public function setId($id);
}
