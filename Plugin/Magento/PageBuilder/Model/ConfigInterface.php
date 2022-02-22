<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

declare(strict_types=1);

namespace Magefan\WysiwygAdvanced\Plugin\Magento\PageBuilder\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigPlugin constructor.
     * @param null $activeEditor
     */
    public function __construct(
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param mixed
     * @param $result
     * @return mixed|null
     */
    public function afterIsEnabled($subject,$result) {
        if (!$result) {
            return $result;
        }

        $type = false;
        if (in_array($this->request->getModuleName(), ['cms', 'catalog'])) {
            $type = $this->request->getModuleName() . '_' . $this->request->getControllerName();
        } elseif ('blog' == $this->request->getModuleName()) {
            $type = $this->request->getModuleName();
        }

        $wysiwygState = $this->scopeConfig->getValue(
            'mfwysiwygadvanced/general/' . $type . '_enabled',
            ScopeInterface::SCOPE_STORE
        );

        $disablePageBuilder = in_array($wysiwygState, [
            \Magento\Cms\Model\Wysiwyg\Config::WYSIWYG_ENABLED,
            \Magento\Cms\Model\Wysiwyg\Config::WYSIWYG_DISABLED,
            \Magento\Cms\Model\Wysiwyg\Config::WYSIWYG_HIDDEN
         ]);

        if ($disablePageBuilder) {
            $result = false;
        }
        return $result;
    }

}
