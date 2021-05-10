<?php
/**
 * RequestContactImport
 *
 * PHP version 5
 *
 * @category Class
 * @package  SendinBlue\Client
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * SendinBlue API
 *
 * SendinBlue provide a RESTFul API that can be used with any languages. With this API, you will be able to :   - Manage your campaigns and get the statistics   - Manage your contacts   - Send transactional Emails and SMS   - and much more...  You can download our wrappers at https://github.com/orgs/sendinblue  **Possible responses**   | Code | Message |   | :-------------: | ------------- |   | 200  | OK. Successful Request  |   | 201  | OK. Successful Creation |   | 202  | OK. Request accepted |   | 204  | OK. Successful Update/Deletion  |   | 400  | Error. Bad Request  |   | 401  | Error. Authentication Needed  |   | 402  | Error. Not enough credit, plan upgrade needed  |   | 403  | Error. Permission denied  |   | 404  | Error. Object does not exist |   | 405  | Error. Method not allowed  |   | 406  | Error. Not Acceptable  |
 *
 * OpenAPI spec version: 3.0.0
 * Contact: contact@sendinblue.com
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 * Swagger Codegen version: 2.4.12
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace SendinBlue\Client\Model;

use \ArrayAccess;
use \SendinBlue\Client\ObjectSerializer;

/**
 * RequestContactImport Class Doc Comment
 *
 * @category Class
 * @package  SendinBlue\Client
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class RequestContactImport implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'requestContactImport';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'fileUrl' => 'string',
        'fileBody' => 'string',
        'listIds' => 'int[]',
        'notifyUrl' => 'string',
        'newList' => '\SendinBlue\Client\Model\RequestContactImportNewList',
        'emailBlacklist' => 'bool',
        'smsBlacklist' => 'bool',
        'updateExistingContacts' => 'bool',
        'emptyContactsAttributes' => 'bool'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'fileUrl' => 'url',
        'fileBody' => null,
        'listIds' => 'int64',
        'notifyUrl' => 'url',
        'newList' => null,
        'emailBlacklist' => null,
        'smsBlacklist' => null,
        'updateExistingContacts' => null,
        'emptyContactsAttributes' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerFormats()
    {
        return self::$swaggerFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'fileUrl' => 'fileUrl',
        'fileBody' => 'fileBody',
        'listIds' => 'listIds',
        'notifyUrl' => 'notifyUrl',
        'newList' => 'newList',
        'emailBlacklist' => 'emailBlacklist',
        'smsBlacklist' => 'smsBlacklist',
        'updateExistingContacts' => 'updateExistingContacts',
        'emptyContactsAttributes' => 'emptyContactsAttributes'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'fileUrl' => 'setFileUrl',
        'fileBody' => 'setFileBody',
        'listIds' => 'setListIds',
        'notifyUrl' => 'setNotifyUrl',
        'newList' => 'setNewList',
        'emailBlacklist' => 'setEmailBlacklist',
        'smsBlacklist' => 'setSmsBlacklist',
        'updateExistingContacts' => 'setUpdateExistingContacts',
        'emptyContactsAttributes' => 'setEmptyContactsAttributes'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'fileUrl' => 'getFileUrl',
        'fileBody' => 'getFileBody',
        'listIds' => 'getListIds',
        'notifyUrl' => 'getNotifyUrl',
        'newList' => 'getNewList',
        'emailBlacklist' => 'getEmailBlacklist',
        'smsBlacklist' => 'getSmsBlacklist',
        'updateExistingContacts' => 'getUpdateExistingContacts',
        'emptyContactsAttributes' => 'getEmptyContactsAttributes'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$swaggerModelName;
    }

    

    

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['fileUrl'] = isset($data['fileUrl']) ? $data['fileUrl'] : null;
        $this->container['fileBody'] = isset($data['fileBody']) ? $data['fileBody'] : null;
        $this->container['listIds'] = isset($data['listIds']) ? $data['listIds'] : null;
        $this->container['notifyUrl'] = isset($data['notifyUrl']) ? $data['notifyUrl'] : null;
        $this->container['newList'] = isset($data['newList']) ? $data['newList'] : null;
        $this->container['emailBlacklist'] = isset($data['emailBlacklist']) ? $data['emailBlacklist'] : false;
        $this->container['smsBlacklist'] = isset($data['smsBlacklist']) ? $data['smsBlacklist'] : false;
        $this->container['updateExistingContacts'] = isset($data['updateExistingContacts']) ? $data['updateExistingContacts'] : true;
        $this->container['emptyContactsAttributes'] = isset($data['emptyContactsAttributes']) ? $data['emptyContactsAttributes'] : false;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets fileUrl
     *
     * @return string
     */
    public function getFileUrl()
    {
        return $this->container['fileUrl'];
    }

    /**
     * Sets fileUrl
     *
     * @param string $fileUrl Mandatory if fileBody is not defined. URL of the file to be imported (no local file). Possible file formats: .txt, .csv
     *
     * @return $this
     */
    public function setFileUrl($fileUrl)
    {
        $this->container['fileUrl'] = $fileUrl;

        return $this;
    }

    /**
     * Gets fileBody
     *
     * @return string
     */
    public function getFileBody()
    {
        return $this->container['fileBody'];
    }

    /**
     * Sets fileBody
     *
     * @param string $fileBody Mandatory if fileUrl is not defined. CSV content to be imported. Use semicolon to separate multiple attributes. Maximum allowed file body size is 10MB . However we recommend a safe limit of around 8 MB to avoid the issues caused due to increase of file body size while parsing. Please use fileUrl instead to import bigger files.
     *
     * @return $this
     */
    public function setFileBody($fileBody)
    {
        $this->container['fileBody'] = $fileBody;

        return $this;
    }

    /**
     * Gets listIds
     *
     * @return int[]
     */
    public function getListIds()
    {
        return $this->container['listIds'];
    }

    /**
     * Sets listIds
     *
     * @param int[] $listIds Mandatory if newList is not defined. Ids of the lists in which the contacts shall be imported. For example, [2, 4, 7].
     *
     * @return $this
     */
    public function setListIds($listIds)
    {
        $this->container['listIds'] = $listIds;

        return $this;
    }

    /**
     * Gets notifyUrl
     *
     * @return string
     */
    public function getNotifyUrl()
    {
        return $this->container['notifyUrl'];
    }

    /**
     * Sets notifyUrl
     *
     * @param string $notifyUrl URL that will be called once the import process is finished. For reference, https://help.sendinblue.com/hc/en-us/articles/360007666479
     *
     * @return $this
     */
    public function setNotifyUrl($notifyUrl)
    {
        $this->container['notifyUrl'] = $notifyUrl;

        return $this;
    }

    /**
     * Gets newList
     *
     * @return \SendinBlue\Client\Model\RequestContactImportNewList
     */
    public function getNewList()
    {
        return $this->container['newList'];
    }

    /**
     * Sets newList
     *
     * @param \SendinBlue\Client\Model\RequestContactImportNewList $newList newList
     *
     * @return $this
     */
    public function setNewList($newList)
    {
        $this->container['newList'] = $newList;

        return $this;
    }

    /**
     * Gets emailBlacklist
     *
     * @return bool
     */
    public function getEmailBlacklist()
    {
        return $this->container['emailBlacklist'];
    }

    /**
     * Sets emailBlacklist
     *
     * @param bool $emailBlacklist To blacklist all the contacts for email
     *
     * @return $this
     */
    public function setEmailBlacklist($emailBlacklist)
    {
        $this->container['emailBlacklist'] = $emailBlacklist;

        return $this;
    }

    /**
     * Gets smsBlacklist
     *
     * @return bool
     */
    public function getSmsBlacklist()
    {
        return $this->container['smsBlacklist'];
    }

    /**
     * Sets smsBlacklist
     *
     * @param bool $smsBlacklist To blacklist all the contacts for sms
     *
     * @return $this
     */
    public function setSmsBlacklist($smsBlacklist)
    {
        $this->container['smsBlacklist'] = $smsBlacklist;

        return $this;
    }

    /**
     * Gets updateExistingContacts
     *
     * @return bool
     */
    public function getUpdateExistingContacts()
    {
        return $this->container['updateExistingContacts'];
    }

    /**
     * Sets updateExistingContacts
     *
     * @param bool $updateExistingContacts To facilitate the choice to update the existing contacts
     *
     * @return $this
     */
    public function setUpdateExistingContacts($updateExistingContacts)
    {
        $this->container['updateExistingContacts'] = $updateExistingContacts;

        return $this;
    }

    /**
     * Gets emptyContactsAttributes
     *
     * @return bool
     */
    public function getEmptyContactsAttributes()
    {
        return $this->container['emptyContactsAttributes'];
    }

    /**
     * Sets emptyContactsAttributes
     *
     * @param bool $emptyContactsAttributes To facilitate the choice to erase any attribute of the existing contacts with empty value. emptyContactsAttributes = true means the empty fields in your import will erase any attribute that currently contain data in SendinBlue, & emptyContactsAttributes = false means the empty fields will not affect your existing data ( only available if `updateExistingContacts` set to true )
     *
     * @return $this
     */
    public function setEmptyContactsAttributes($emptyContactsAttributes)
    {
        $this->container['emptyContactsAttributes'] = $emptyContactsAttributes;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
            return json_encode(
                ObjectSerializer::sanitizeForSerialization($this),
                JSON_PRETTY_PRINT
            );
        }

        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


