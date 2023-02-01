<?php

/**
 * Description of resourceDescriptor
 *
 * @author michele
 */
require_once(ITA_LIB_PATH . '/itaPHPJasper/resourceProperty.class.php');

class resourceDescriptor {
// Resource wsTypes
    const TYPE_FOLDER = "folder";
    const TYPE_REPORTUNIT = "reportUnit";
    const TYPE_DATASOURCE = "datasource";
    const TYPE_DATASOURCE_JDBC = "jdbc";
    const TYPE_DATASOURCE_JNDI = "jndi";
    const TYPE_DATASOURCE_BEAN = "bean";
    const TYPE_DATASOURCE_CUSTOM = "custom";
    const TYPE_IMAGE = "img";
    const TYPE_FONT = "font";
    const TYPE_JRXML = "jrxml";
    const TYPE_CLASS_JAR = "jar";
    const TYPE_RESOURCE_BUNDLE = "prop";
    const TYPE_REFERENCE = "reference";
    const TYPE_INPUT_CONTROL = "inputControl";
    const TYPE_DATA_TYPE = "dataType";
    const TYPE_OLAP_MONDRIAN_CONNECTION = "olapMondrianCon";
    const TYPE_OLAP_XMLA_CONNECTION = "olapXmlaCon";
    const TYPE_MONDRIAN_SCHEMA = "olapMondrianSchema";
    const TYPE_ACCESS_GRANT_SCHEMA = "accessGrantSchema"; // Pro-only
    const TYPE_UNKNOW = "unknow";
    const TYPE_LOV = "lov"; // List of values...
    const TYPE_QUERY = "query"; // List of values...
    const TYPE_CONTENT_RESOURCE = "contentResource";
    const TYPE_STYLE_TEMPLATE = "jrtx";

    /**
     * These constants are copied here from DataType for facility
     */
    const DT_TYPE_TEXT = 1;
    const DT_TYPE_NUMBER = 2;
    const DT_TYPE_DATE = 3;
    const DT_TYPE_DATE_TIME = 4;

    /**
     * These constants are copied here from InputControl for facility
     */
    const IC_TYPE_BOOLEAN = 1;
    const IC_TYPE_SINGLE_VALUE = 2;
    const IC_TYPE_SINGLE_SELECT_LIST_OF_VALUES = 3;
    const IC_TYPE_SINGLE_SELECT_QUERY = 4;
    const IC_TYPE_MULTI_VALUE = 5;
    const IC_TYPE_MULTI_SELECT_LIST_OF_VALUES = 6;
    const IC_TYPE_MULTI_SELECT_QUERY = 7;
    const IC_TYPE_SINGLE_SELECT_LIST_OF_VALUES_RADIO = 8;
    const IC_TYPE_SINGLE_SELECT_QUERY_RADIO = 9;
    const IC_TYPE_MULTI_SELECT_LIST_OF_VALUES_CHECKBOX = 10;
    const IC_TYPE_MULTI_SELECT_QUERY_CHECKBOX = 11;

    const PROP_VERSION = "PROP_VERSION";
    const PROP_PARENT_FOLDER = "PROP_PARENT_FOLDER";
    const PROP_RESOURCE_TYPE = "PROP_RESOURCE_TYPE";
    const PROP_CREATION_DATE = "PROP_CREATION_DATE";

// File resource properties
    const PROP_FILERESOURCE_HAS_DATA = "PROP_HAS_DATA";
    const PROP_FILERESOURCE_IS_REFERENCE = "PROP_IS_REFERENCE";
    const PROP_FILERESOURCE_REFERENCE_URI = "PROP_REFERENCE_URI";
    const PROP_FILERESOURCE_WSTYPE = "PROP_WSTYPE";

// Datasource properties
    const PROP_DATASOURCE_DRIVER_CLASS = "PROP_DATASOURCE_DRIVER_CLASS";
    const PROP_DATASOURCE_CONNECTION_URL = "PROP_DATASOURCE_CONNECTION_URL";
    const PROP_DATASOURCE_USERNAME = "PROP_DATASOURCE_USERNAME";
    const PROP_DATASOURCE_PASSWORD = "PROP_DATASOURCE_PASSWORD";
    const PROP_DATASOURCE_JNDI_NAME = "PROP_DATASOURCE_JNDI_NAME";
    const PROP_DATASOURCE_BEAN_NAME = "PROP_DATASOURCE_BEAN_NAME";
    const PROP_DATASOURCE_BEAN_METHOD = "PROP_DATASOURCE_BEAN_METHOD";
    const PROP_DATASOURCE_CUSTOM_SERVICE_CLASS = "PROP_DATASOURCE_CUSTOM_SERVICE_CLASS";
    const PROP_DATASOURCE_CUSTOM_PROPERTY_MAP = "PROP_DATASOURCE_CUSTOM_PROPERTY_MAP";

// ReportUnit resource properties
    const PROP_RU_DATASOURCE_TYPE = "PROP_RU_DATASOURCE_TYPE";
    const PROP_RU_IS_MAIN_REPORT = "PROP_RU_IS_MAIN_REPORT";
    const PROP_RU_INPUTCONTROL_RENDERING_VIEW = "PROP_RU_INPUTCONTROL_RENDERING_VIEW";
    const PROP_RU_REPORT_RENDERING_VIEW = "PROP_RU_REPORT_RENDERING_VIEW";
    const PROP_RU_ALWAYS_PROPMT_CONTROLS = "PROP_RU_ALWAYS_PROPMT_CONTROLS";
    const PROP_RU_CONTROLS_LAYOUT = "PROP_RU_CONTROLS_LAYOUT";
    const RU_CONTROLS_LAYOUT_POPUP_SCREEN = 1;
    const RU_CONTROLS_LAYOUT_SEPARATE_PAGE = 2;
    const RU_CONTROLS_LAYOUT_TOP_OF_PAGE = 3;

// DataType resource properties
    const PROP_DATATYPE_STRICT_MAX = "PROP_DATATYPE_STRICT_MAX";
    const PROP_DATATYPE_STRICT_MIN = "PROP_DATATYPE_STRICT_MIN";
    const PROP_DATATYPE_MIN_VALUE = "PROP_DATATYPE_MIN_VALUE";
    const PROP_DATATYPE_MAX_VALUE = "PROP_DATATYPE_MAX_VALUE";
    const PROP_DATATYPE_PATTERN = "PROP_DATATYPE_PATTERN";
    const PROP_DATATYPE_TYPE = "PROP_DATATYPE_TYPE";

// ListOfValues resource properties
    const PROP_LOV = "PROP_LOV";
    const PROP_LOV_LABEL = "PROP_LOV_LABEL";
    const PROP_LOV_VALUE = "PROP_LOV_VALUE";


// InputControl resource properties
    const PROP_INPUTCONTROL_TYPE = "PROP_INPUTCONTROL_TYPE";
    const PROP_INPUTCONTROL_IS_MANDATORY = "PROP_INPUTCONTROL_IS_MANDATORY";
    const PROP_INPUTCONTROL_IS_READONLY = "PROP_INPUTCONTROL_IS_READONLY";

// SQL resource properties
    const PROP_QUERY = "PROP_QUERY";
    const PROP_QUERY_VISIBLE_COLUMNS = "PROP_QUERY_VISIBLE_COLUMNS";
    const PROP_QUERY_VISIBLE_COLUMN_NAME = "PROP_QUERY_VISIBLE_COLUMN_NAME";
    const PROP_QUERY_VALUE_COLUMN = "PROP_QUERY_VALUE_COLUMN";
    const PROP_QUERY_LANGUAGE = "PROP_QUERY_LANGUAGE";


// SQL resource properties
    const PROP_QUERY_DATA = "PROP_QUERY_DATA";
    const PROP_QUERY_DATA_ROW = "PROP_QUERY_DATA_ROW";
    const PROP_QUERY_DATA_ROW_COLUMN = "PROP_QUERY_DATA_ROW_COLUMN";

// OLAP XMLA Connection
    const PROP_XMLA_URI = "PROP_XMLA_URI";
    const PROP_XMLA_CATALOG = "PROP_XMLA_CATALOG";
    const PROP_XMLA_DATASOURCE = "PROP_XMLA_DATASOURCE";
    const PROP_XMLA_USERNAME = "PROP_XMLA_USERNAME";
    const PROP_XMLA_PASSWORD = "PROP_XMLA_PASSWORD";

// Content resource properties
    const PROP_CONTENT_RESOURCE_TYPE = "CONTENT_TYPE";
    const PROP_DATA_ATTACHMENT_ID = "DATA_ATTACHMENT_ID";
    const CONTENT_TYPE_PDF = "pdf";
    const CONTENT_TYPE_HTML = "html";
    const CONTENT_TYPE_XLS = "xls";
    const CONTENT_TYPE_RTF = "rtf";
    const CONTENT_TYPE_CSV = "csv";
    const CONTENT_TYPE_IMAGE = "img";
// Arguments
    const MODIFY_REPORTUNIT = "MODIFY_REPORTUNIT_URI";
    const CREATE_REPORTUNIT = "CREATE_REPORTUNIT_BOOLEAN";
    const LIST_DATASOURCES = "LIST_DATASOURCES";
    const IC_GET_QUERY_DATA = "IC_GET_QUERY_DATA";

    const VALUE_TRUE = "true";
    const VALUE_FALSE = "false";

    const RUN_OUTPUT_FORMAT = "RUN_OUTPUT_FORMAT";
    const RUN_OUTPUT_FORMAT_PDF = "PDF";
    const RUN_OUTPUT_FORMAT_JRPRINT = "JRPRINT";
    const RUN_OUTPUT_FORMAT_HTML = "HTML";
    const RUN_OUTPUT_FORMAT_XLS = "XLS";
    const RUN_OUTPUT_FORMAT_XML = "XML";
    const RUN_OUTPUT_FORMAT_CSV = "CSV";
    const RUN_OUTPUT_FORMAT_RTF = "RTF";
    const RUN_OUTPUT_IMAGES_URI = "IMAGES_URI";
    const RUN_OUTPUT_PAGE = "PAGE";

    const LIST_RESOURCES = "LIST_RESOURCES";
    const RESOURCE_TYPE = "RESOURCE_TYPE";
    const REPORT_TYPE = "REPORT_TYPE";
    const START_FROM_DIRECTORY = "START_FROM_DIRECTORY";
    const NO_RESOURCE_DATA_ATTACHMENT = "NO_ATTACHMENT";
    const NO_SUBRESOURCE_DATA_ATTACHMENTS = "NO_SUBRESOURCE_ATTACHMENTS";
    const DESTINATION_URI = "DESTINATION_URI";
    // the following come
    // from Resource interface
    // Main Attributes
    private $name;
    private $label;
    private $description;
    private $isNew = false;
    private $wsType;   // this it object/xml type
    private $uriString;
    private $creationDate;
    private $children = array(); // list of ResourceDescriptors
    private $parameters = array(); // list of ListItem
    private $properties = array();
// This data is used to store the data for sunsequent calls to getQueryData....
    private $queryDataCache = array();

    public function getWsType() {
        return $this->wsType;
    }

    public function setWsType($wsType) {
        $this->wsType = $wsType;
    }

    public function getUriString() {
        return $this->uriString;
    }

    public function setUriString($uriString) {
        $this->uriString = $uriString;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getLabel() {
        return $this->label;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Return the value for the property PROP_VERSION.
     * If no version is set, 0 is returned
     */
    public function getVersion() {
        $i = $this->getResourcePropertyValueAsInteger(self::PROP_VERSION);
        if ($i != null)
            return $i;
        return 0;
    }

    public function setVersion($version) {
        $this->setResourceProperty(self::PROP_VERSION, $version);
    }

// this is a uri string (like uriString member)
    public function getParentFolder() {
        return $this->getResourcePropertyValue(self::PROP_PARENT_FOLDER);
    }

    public function setParentFolder($parentFolder) {
        $this->setResourceProperty(self::PROP_PARENT_FOLDER, $parentFolder);
        ;
    }

    public function getResourceType() {
        return $this->getResourcePropertyValue(self::PROP_RESOURCE_TYPE);
    }

    public function setResourceType($resourceType) {
        $this->setResourceProperty(self::PROP_RESOURCE_TYPE, $resourceType);
    }

    public function getIsNew() {
        return $this->isNew;
    }

    public function setIsNew($isNew) {
        $this->isNew = $isNew;
    }

    public function getCreationDate() {
        return $this->creationDate;
    }

    public function setCreationDate($creationDate) {
        $this->creationDate = $creationDate;
    }

    public function getHasData() {
        $s = $this->getResourcePropertyValue(self::PROP_FILERESOURCE_HAS_DATA);
        if ($s != null)
            return true;
        return false;
    }

    public function setHasData($hasData) {
        $this->setResourceProperty(self::PROP_FILERESOURCE_HAS_DATA, $hasData);
    }

    public function getIsReference() {
        $s = $this->getResourcePropertyValue(self::PROP_FILERESOURCE_IS_REFERENCE);
        if ($s != null)
            return true;
        return false;
    }

    public function setIsReference($isReference) {
        $this->setResourceProperty(self::PROP_FILERESOURCE_IS_REFERENCE, $isReference);
    }

    public function getReferenceUri() {
        return $this->getResourcePropertyValue(self::PROP_FILERESOURCE_REFERENCE_URI);
    }

    public function setReferenceUri($referenceUri) {
        $this->setResourceProperty(self::PROP_FILERESOURCE_REFERENCE_URI, $referenceUri);
    }

    public function getDriverClass() {
        return $this->getResourcePropertyValue(self::PROP_DATASOURCE_DRIVER_CLASS);
    }

    public function setDriverClass($driverClass) {
        $this->setResourceProperty(self::PROP_DATASOURCE_DRIVER_CLASS, $driverClass);
    }

    public function getConnectionUrl() {
        return $this->getResourcePropertyValue(self::PROP_DATASOURCE_CONNECTION_URL);
    }

    public function setConnectionUrl($connectionUrl) {
        $this->setResourceProperty(self::PROP_DATASOURCE_CONNECTION_URL, $connectionUrl);
    }

    public function getPassword() {
        return $this->getResourcePropertyValue(self::PROP_DATASOURCE_PASSWORD);
    }

    public function setPassword($password) {
        $this->setResourceProperty(self::PROP_DATASOURCE_PASSWORD, $password);
    }

    public function getUsername() {
        return $this->getResourcePropertyValue(self::PROP_DATASOURCE_USERNAME);
    }

    public function setUsername($username) {
        $this->setResourceProperty(self::PROP_DATASOURCE_USERNAME, username);
    }

    public function getJndiName() {
        return $this->getResourcePropertyValue(self::PROP_DATASOURCE_JNDI_NAME);
    }

    public function setJndiName($jndiName) {
        $this->setResourceProperty(self::PROP_DATASOURCE_JNDI_NAME, $jndiName);
    }

    public function setServiceClass($svcClass) {
        $this->setResourceProperty(self::PROP_DATASOURCE_CUSTOM_SERVICE_CLASS, $svcClass);
    }

    public function getServiceClass() {
        return $this->getResourcePropertyValue(self::PROP_DATASOURCE_CUSTOM_SERVICE_CLASS);
    }

    public function setPropertyMap($map) {
//  Verificare
//        $rp = new ResourceProperty(self::PROP_DATASOURCE_CUSTOM_PROPERTY_MAP);
//        $ki = $map;
//        foreach ($ki as $k => $value) {
//            $rp->getProperties()->add(new ResourceProperty($k, $map[$k]));
//        }
//        $this->setResourceProperty($rp);
    }

    public function getPropertyMap() {
        $rp = $this->getResourceProperty(self::PROP_DATASOURCE_CUSTOM_PROPERTY_MAP);
        $map = array();
        if ($rp != null) {
            foreach ($rp as $key => $value) {
                $rpChild = $rp->getProperties() . get(i);
                $map[$rpChild . getName()] = $rpChild . getValue();
            }
        }
        return $map;
    }

    public function getDataSourceType() {
        return $this->getResourcePropertyValue(self::PROP_RU_DATASOURCE_TYPE);
    }

    public function setDataSourceType($dataSourceType) {
        $this->setResourceProperty(self::PROP_RU_DATASOURCE_TYPE, $dataSourceType);
    }

    public function isMainReport() {
        $s = $this->getResourcePropertyValue(self::PROP_RU_IS_MAIN_REPORT);
        if ($s != null)
            return true;
        return false;
    }

    public function setMainReport($isMainReport) {
        $this->setResourceProperty(self::PROP_RU_IS_MAIN_REPORT, $isMainReport);
    }

    public function getChildren() {
        return $this->children;
    }

    public function setChildren($children) {
        $this->children = $children;
    }

    public function isStrictMax() {
        $s = $this->getResourcePropertyValue(self::PROP_DATATYPE_STRICT_MAX);
        if ($s != null)
            return true;
        return false;
    }

    public function setStrictMax($strictMax) {
        $this->setResourceProperty(self::PROP_DATATYPE_STRICT_MAX, $strictMax);
    }

    public function isStrictMin() {
        $s = $this->getResourcePropertyValue(self::PROP_DATATYPE_STRICT_MIN);
        if ($s != null)
            return true;
        return false;
    }

    public function setStrictMin($strictMin) {
        $this->setResourceProperty(self::PROP_DATATYPE_STRICT_MIN, $strictMin);
    }

    public function getMinValue() {
        return $this->getResourcePropertyValue(self::PROP_DATATYPE_MIN_VALUE);
    }

    public function setMinValue($minValue) {
        $this->setResourceProperty(self::PROP_DATATYPE_MIN_VALUE, $minValue);
    }

    public function getMaxValue() {
        return $this->getResourcePropertyValue(self::PROP_DATATYPE_MAX_VALUE);
    }

    public function setMaxValue($maxValue) {
        $this->setResourceProperty(self::PROP_DATATYPE_MAX_VALUE, $maxValue);
    }

    public function getPattern() {
        return $this->getResourcePropertyValue(self::PROP_DATATYPE_PATTERN);
    }

    public function setPattern($pattern) {
        $this->setResourceProperty(self::PROP_DATATYPE_PATTERN, $pattern);
    }

    public function getDataType() {
        $s = $this->getResourcePropertyValue(self::PROP_DATATYPE_TYPE);
        if ($s == null || $s == "")
            return 0;
        return $s;
    }

    public function setDataType($dataType) {
        $this->setResourceProperty(self::PROP_DATATYPE_TYPE, $dataType);
    }

    /**
     *  Returns the property PROP_LOV as a list of ListItem....
     *  Columns name are looked for in the property PROP_LOV,
     *  name="LABEL" and value="value"
     *  i.e.
     *  <resourceProperty name="PROP_LOV">
     *      <resourceProperty name="1">
     *              <value>test1</value>
     *      </resourceProperty>
     *      <resourceProperty name="2">
     *              <value>test2</value>
     *      </resourceProperty>
     *  </resourceProperty>
     *  are in the list.
     */
    public function getListOfValues() {

        $rp = $this->getResourceProperty(self::PROP_LOV);

        $listOfValues = array();
//if ($rp != null)
//{
//$tmpArr=$rp->getProperties();
//foreach ($tmpArr as $key => $value) {
//
//$rpChild = $tmp[$key];
//$li = $rpChild.getValue() != null ? $rpChild.getValue() : $rpChild.getName();
//$listOfValues[]=$li;
//}
//}

        return $listOfValues;
    }

    /**
     * Convenient way to create tje LOV property from a list of ListItem
     */
    public function setListOfValues($listOfValues) {

//ResourceProperty rp = new ResourceProperty( PROP_LOV );
//
//for (int i=0;
//i<listOfValues.size();
//++i)
//{
//ListItem li = (ListItem)listOfValues.get(i);
//rp.getProperties().add( new ResourceProperty(li.getValue()+"", li.getLabel() ) );
//}
//
//setResourceProperty(rp);
    }

    public function getControlType() {
        $s = $this->getResourcePropertyValue(self::PROP_INPUTCONTROL_TYPE);
        if ($s == null || $s == "")
            return 0;
        return $s;
    }

    public function setControlType($controlType) {
        $this->setResourceProperty(self::PROP_INPUTCONTROL_TYPE, $controlType);
    }

    public function isMandatory() {
        $s = $this->getResourcePropertyValue(self::PROP_INPUTCONTROL_IS_MANDATORY);
        if ($s != null)
            return true;
        return false;
    }

    public function setMandatory($mandatory) {
        $this->setResourceProperty(self::PROP_INPUTCONTROL_IS_MANDATORY, $mandatory);
    }

    public function isReadOnly() {
        $s = $this->getResourcePropertyValue(self::PROP_INPUTCONTROL_IS_READONLY);
        if (s != null)
            return true;
        return false;
    }

    public function setReadOnly($readOnly) {
        $this->setResourceProperty(self::PROP_INPUTCONTROL_IS_READONLY, $readOnly);
    }

    public function isVisible() {
        $s = $this->getResourcePropertyValue(self::PROP_INPUTCONTROL_IS_VISIBLE);
        if (s != null)
            return true;
        return true;
    }

    public function setVisible($visible) {
        $this->setResourceProperty(self::PROP_INPUTCONTROL_IS_VISIBLE, $visible);
    }

    public function getBeanName() {
        return $this->getResourcePropertyValue(self::PROP_DATASOURCE_BEAN_NAME);
    }

    public function setBeanName($beanName) {
        $this->setResourceProperty(self::PROP_DATASOURCE_BEAN_NAME, $beanName);
    }

    public function getBeanMethod() {
        return $this->getResourcePropertyValue(self::PROP_DATASOURCE_BEAN_METHOD);
    }

    public function setBeanMethod($beanMethod) {
        $this->setResourceProperty(self::PROP_DATASOURCE_BEAN_METHOD, $beanMethod);
    }

    public function getSql() {
        return $this->getResourcePropertyValue(self::PROP_QUERY);
    }

    public function setSql($sql) {
        $this->setResourceProperty(self::PROP_QUERY, $sql);
    }

    /**
     *  Return the set of visible columns as a String array....
     *  Columns name are looked for in the property PROP_QUERY_VISIBLE_COLUMNS,
     *  all children of this property with type  PROP_QUERY_VISIBLE_COLUMN_NAME
     *  are in the list.
     */
    public function getQueryVisibleColumns() {
//$rp = getResourceProperty(self::PROP_QUERY_VISIBLE_COLUMNS );
//
//$columnList = array();
//if ($rp != null){
//
//
//for (int i=0;i<rp.getProperties().size();++i){
//ResourceProperty rpChild = (ResourceProperty)rp.getProperties().get(i);
//if (rpChild.getName().equals( PROP_QUERY_VISIBLE_COLUMN_NAME )){
//    columnList.add( rpChild.getValue());
//    }
//}
//
//String[] columns = new String[columnList.size()];
//for (int i=0;
//i<columnList.size();
//++i)
//{
//columns[i] = "" + columnList.get(i);
//}
//
//return columns;
//}
//return new String[0];
    }

    /**
     * Set the list of columns using a String array
     * The result is a new ResourceProperty (PROP_QUERY_VISIBLE_COLUMNS) filled with a set
     * of children, one per column.
     */
    public function setQueryVisibleColumns($queryVisibleColumns) {
//
//ResourceProperty rp = new ResourceProperty(PROP_QUERY_VISIBLE_COLUMNS);
//
//for (int i=0;
//i<queryVisibleColumns.length;
//++i)
//{
//rp.getProperties().add( new ResourceProperty(PROP_QUERY_VISIBLE_COLUMN_NAME, queryVisibleColumns[i]));
//}
//
//setResourceProperty(rp);
//}
//
//public String getQueryValueColumn() {
//return $this->getResourcePropertyValue(self::PROP_QUERY_VALUE_COLUMN );
//}
//
//public void setQueryValueColumn(String queryValueColumn) {
//setResourceProperty(PROP_QUERY_VALUE_COLUMN, queryValueColumn );
    }

    /**
     *  Return the property PROP_QUERY_DATA as set of InputControlQueryDataRow
     *  the structure is as follow:
     *   PROP_QUERY_DATA { PROP_QUERY_DATA_ROW { PROP_QUERY_DATA_COLUMN_VALUE } } }
     *  This method is performed only once, and the result is cached in queryDataCache. Subsequent calls
     *  to this method will return always queryDataCache.
     *
     */
    public function getQueryData() {

//if (queryDataCache != null) return queryDataCache;
//
//queryDataCache = new java.util.ArrayList();
//
//ResourceProperty rp = getResourceProperty( PROP_QUERY_DATA );
//if (rp != null)
//{
//// Look for rows....
//for (int i=0;
//i<rp.getProperties().size();
//++i)
//{
//ResourceProperty rpRow = (ResourceProperty)rp.getProperties().get(i);
//if (rpRow.getName().equals( PROP_QUERY_DATA_ROW ))
//{
//InputControlQueryDataRow icqdr = new InputControlQueryDataRow();
//icqdr.setValue( rpRow.getValue() );
//
//// Look for row details...
//for (int k=0;
//k<rpRow.getProperties().size();
//++k)
//{
//ResourceProperty rpRowChild = (ResourceProperty)rpRow.getProperties().get(k);
//if (rpRowChild.getName().equals( PROP_QUERY_DATA_ROW_COLUMN ))
//{
//icqdr.getColumnValues().add( rpRowChild.getValue() );
//}
//}
//
//queryDataCache.add(icqdr );
//}
//}
//
//}
//return queryDataCache;
    }

    /**
     *  Convenient way to create the PROP_QUERY_DATA properties from a set of InputControlQueryDataRow
     *  the structure will be create as follow:
     *   PROP_QUERY_DATA { PROP_QUERY_DATA_ROW { PROP_QUERY_DATA_COLUMN_VALUE } } }
     *  A call to this method will set to null the queryDataCache
     *
     */
    public function setQueryData($queryData) {
//
//queryDataCache = null;
//
//ResourceProperty rp = new ResourceProperty(PROP_QUERY_DATA);
//
//for (int i=0;
//i<queryData.size();
//++i)
//{
//InputControlQueryDataRow icqdr = (InputControlQueryDataRow)queryData.get(i);
//
//ResourceProperty rpRow = new ResourceProperty(PROP_QUERY_DATA_ROW, "" + icqdr.getValue());
//
//for (int k=0;
//k<icqdr.getColumnValues().size();
//++k)
//{
//Object columnValue = icqdr.getColumnValues().get(k);
//rpRow.getProperties().add( new ResourceProperty( PROP_QUERY_DATA_ROW_COLUMN, (columnValue == null) ? "" : ""+columnValue));
//}
//
//rp.getProperties().add( rpRow );
//}
//
//setResourceProperty(rp);
//
    }

//
///**
// * Return the List of properties. Don't add properties directly!
// * Use setResourceProperty instead!
// */
    public function getProperties() {
        return $this->properties;
    }

    /**
     * Replace all the properties with the specified list. The internal hashmap is
     * updated.
     */
    public function setProperties($properties) {
        $this->properties = $properties;
    }

    /**
     * Setting a property to a null value is the same as remove it.
     *
     */
    public function setResourceProperty($resourcePropertyName, $value) {
        if ($resourcePropertyName == null)
            return;
        if ($value == null) {
            $this->removeResourceProperty($resourcePropertyName);
        } else {
            $this->properties[$resourcePropertyName] = $value;
        }
    }

    /**
     * Add or replace the resource property in the
     * ResourceDescriptor.
     */
//public void setResourceProperty(ResourceProperty rp)
//{
//if (rp == null) return;
//removeResourceProperty( rp.getName());
//this.getProperties().add( rp );
//this.hm.put( rp.getName(), rp);
//}

    /**
     * Remove all resources with name = resourcePropertyName
     */
    public function removeResourceProperty($resourcePropertyName) {
        if (array_key_exists($resourcePropertyName, $this->properties)) {
            unset($this->properties[$resourcePropertyName]);
        }
    }

    public function getResourceProperty($resourcePropertyName) {
        return $this->properties[$resourcePropertyName];
    }

    /**
     * Return the value of the property resourcePropertyName as String
     * Return null if the property is not found or the [operty value is null.
     *
     */
    public function getResourcePropertyValue($resourcePropertyName) {
        return $this->properties[$resourcePropertyName];
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    public function toXML($exportChildren=false) {
        $xml = "<resourceDescriptor ";
        $xml .= 'name = "' . $this->getName() . '" ';
        $xml .= 'wsType = "' . $this->getWsType() . '" ';
        $xml .= 'uriString = "' . $this->getUriString() . '" ';
        $xml .= 'isNew = "' . $this->getIsNew() . '" ';
        if ($this->getCreationDate()) {
            $xml .= 'creationDate = "' . $this->getCreationDate() . '" ';
        }
        $xml .= ">";
        $xml .= "<label><![CDATA[" . $this->getLabel() . "]]></label>";
        $xml .= "<description><![CDATA[" . $this->getDescription() . "]]></description>";
        if ($exportChildren) {
            $children = $this->getChildren();
            foreach ($children as $rd_child) {
                $xml .= $rd_child->toXml();
            }
        }
        $parameters = $this->getParameters();
        foreach ($parameters as $key => $value) {
            $xml .= '<parameter name="' . $key . '">' . htmlspecialchars(utf8_encode($value)) . '</parameter>';
        }

        $properties = $this->getProperties();
        foreach ($properties as $key => $value) {
            $xml .= '<resourceProperty name="' . $key . '">';
            $xml .= '   <value><![CDATA[' . $value . ']]></value>';
            $xml .= '</resourceProperty>';
        }
        $xml .="</resourceDescriptor>";
        return $xml;
    }

}

?>
