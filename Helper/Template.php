<?php

namespace Etailors\Forms\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class Template extends \Magento\Framework\App\Helper\AbstractHelper
{
    const REGEX_IF = '/{{if (!?)([a-z\.-]+)}}(.*){{endif}}/Um';
    const REGEX_VAR = '/{{var ([a-z\.-]+)}}/Um';
    const REGEX_UNPROCESSED = '/{{(.*)}}/Um';
    const REGEX_EMPTY_PARAGRAPH = '/<p[^>]*><\\/p[^>]*>/Um';
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Etailors\Forms\Model\Form
     */
    protected $form;
    
    /**
     * @var \Etailors\Forms\Model\Email
     */
    protected $email;
    
    /**
     * @var string
     */
    protected $content;
    
    /**
     * @var array
     */
    protected $vars = [];
    
    /**
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }
    
    /**
     * @param \Etailors\Forms\Model\Form $form
     * @return void
     */
    public function setForm($form)
    {
        $this->form = $form;
    }
    
    /**
     * @param boolean $addAnswer
     * @return array
     */
    public function getTemplateVars($addAnswer = false)
    {
        $this->vars['store'] = $this->storeManager->getStore($this->email->getStoreId());
        $this->vars['form'] = [];
        foreach ($this->form->getFields() as $field) {
            $fieldCode = $this->translitFieldName($field->getTitle());
            // Using fieldCode as array key for easy searching
            $this->vars['form'][$fieldCode] = [
                'code' => $fieldCode,
                'label' => $field->getTitle()
            ];
            
            if ($addAnswer) {
                $this->vars['form'][$fieldCode]['value'] = $this->email->getFieldAnswer($field);
            }
        }

        return $this->vars;
    }
    
    /**
     * @param string                      $content
     * @param \Etailors\Forms\Model\Form  $form
     * @param \Etailors\Forms\Model\Email $email
     * @return string
     */
    public function processRaw($content, $form, $email)
    {
        $this->form = $form;
        $this->email = $email;
        $this->content = $content;
        
        $templateVars = $this->getTemplateVars(true);
        
        $this->processIfStatements();
        $this->processVars();
        
        $this->cleanUpUnprocessedVarTags();
        $this->cleanUpHtml();
        
        return $this->content;
    }
    
    /**
     * @param string $fieldName
     * @return string
     */
    protected function translitFieldName($fieldName)
    {
        // replace non letter or digits by -
        $fieldName = preg_replace('~[^\pL\d]+~u', '-', $fieldName);

        // transliterate
        $fieldName = iconv('utf-8', 'us-ascii//TRANSLIT', $fieldName);

        // remove unwanted characters
        $fieldName = preg_replace('~[^-\w]+~', '', $fieldName);

        // trim
        $fieldName = trim($fieldName, '-');

        // remove duplicate -
        $fieldName = preg_replace('~-+~', '-', $fieldName);

        // lowercase
        $fieldName = strtolower($fieldName);

        if (empty($fieldName)) {
            return 'n-a';
        }

        return $fieldName;
    }
    
    /**
     * @return void
     */
    protected function cleanUpUnprocessedVarTags()
    {
        $this->content = preg_replace(self::REGEX_UNPROCESSED, '', $this->content);
    }
    
    /**
     * @return void
     */
    protected function cleanUpHtml()
    {
        $this->content = preg_replace(self::REGEX_EMPTY_PARAGRAPH, '', $this->content);
    }
    
    /**
     * @return void
     */
    protected function processVars()
    {
        preg_match_all(self::REGEX_VAR, $this->content, $output);
        
        $pregKeys = [
            'full',
            'variable'
        ];
        
        if (isset($output[0]) && !empty($output[0])) {
            $vars = $this->translitPregMatchOutput($output, $pregKeys);
            
            foreach ($vars as $var) {
                $this->applyVariable($var);
            }
        }
    }
    
    /**
     * @return void
     */
    protected function applyVariable($variable)
    {
        $value = $this->getVariableValue($variable['variable']);
        $regex = '#' . preg_quote($variable['full'], '#') . '#Um';
        $this->content = preg_replace($regex, $value, $this->content);
    }
    
    /**
     * @return void
     */
    protected function processIfStatements()
    {
        preg_match_all(self::REGEX_IF, $this->content, $output);
        
        $pregKeys = [
            'full',
            'notOperator',
            'condition',
            'value'
        ];
        
        if (isset($output[0]) && !empty($output[0])) {
            $ifStatements = $this->translitPregMatchOutput($output, $pregKeys);
            
            foreach ($ifStatements as $ifStatement) {
                $this->applyIfStatement($ifStatement);
            }
        }
    }
    
    /**
     * @param array $ifStatement
     * @return void
     */
    protected function applyIfStatement($ifStatement)
    {
        $value = $this->getVariableValue($ifStatement['condition']);
        $regex = '#' . $ifStatement['full'] . '#Um';

        if ($ifStatement['notOperator'] === '!' && empty($value)) {
            $this->content = preg_replace($regex, $ifStatement['value'], $this->content);
        } elseif ($ifStatement['notOperator'] !== '!' && !empty($value)) {
            $this->content = preg_replace($regex, $ifStatement['value'], $this->content);
        } else {
            $this->content = preg_replace($regex, '', $this->content);
        }
    }
    
    /**
     * @param array $output
     * @param array $keys
     * @return array
     */
    protected function translitPregMatchOutput($output, $keys = [])
    {
        $translitted = [];
        foreach ($output as $dataKey => $matches) {
            foreach ($matches as $matchKey => $matchValue) {
                if (!isset($translitted[$matchKey])) {
                    $translitted[$matchKey] = [];
                }
                
                if (!empty($keys)) {
                    $translitted[$matchKey][$keys[$dataKey]] = $matchValue;
                } else {
                    $translitted[$matchKey][$dataKey] = $matchValue;
                }
            }
        }

        return $translitted;
    }
    
    /**
     * @param string $variable
     * @return string
     */
    protected function getVariableValue($variable)
    {
        if ($variable === 'form.values') {
            return $this->getAllFormAnswersAsTable();
        }
        $variableParts = explode('.', $variable);
        $value = $this->vars;
        for ($i = 0; $i < count($variableParts); $i++) {
            if (!isset($value[$variableParts[$i]])) {
                return false;
            }
            $value = $value[$variableParts[$i]];
        }

        return $value;
    }
    
    /**
     * @return string
     */
    protected function getAllFormAnswersAsTable()
    {
        $table = '<table>';
        foreach ($this->form->getFields() as $field) {
            $table .= '<tr>';
            $table .= '<td>';
            $table .= '<b>' . $field->getTitle() . '</b>';
            $table .= '</td>';
            $table .= '<td>';
            $table .= $this->email->getFieldAnswer($field);
            $table .= '</td>';
            $table .= '</tr>';
        }
        $table .= '</table>';

        return $table;
    }
}
