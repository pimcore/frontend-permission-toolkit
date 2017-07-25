<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */


class FrontendPermissionToolkit_Plugin  extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface {

    public function init() {
        // register your events here

        // using anonymous function
        Pimcore::getEventManager()->attach("document.postAdd", function ($event) {
            // do something
            $document = $event->getTarget();
        });

        // using methods
        Pimcore::getEventManager()->attach("document.postUpdate", array($this, "handleDocument"));

        // for more information regarding events, please visit:
        // http://www.pimcore.org/wiki/display/PIMCORE/Event+API+%28EventManager%29+since+2.1.1
        // http://framework.zend.com/manual/1.12/de/zend.event-manager.event-manager.html
        // http://www.pimcore.org/wiki/pages/viewpage.action?pageId=12124202

    }

    public function handleDocument ($event) {
        // do something
        $document = $event->getTarget();
    }

	public static function install (){
        // implement your own logic here
        return true;
	}
	
	public static function uninstall (){
        // implement your own logic here
        return true;
	}

	public static function isInstalled () {
        // implement your own logic here
        return true;
	}

    /**
     *
     * @param string $language
     * @return string path to the translation file relative to plugin direcory
     */
    public static function getTranslationFile($language) {
        if ($language == "de") {
            return "/FrontendPermissionToolkit/texts/de.csv";
        } else if ($language == "en") {
            return "/FrontendPermissionToolkit/texts/en.csv";
        } else {
            return null;
        }
    }

}