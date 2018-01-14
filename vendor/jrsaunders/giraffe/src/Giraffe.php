<?php
namespace Giraffe;
/**
 * Class Giraffe
 * @package Giraffe
 */
class Giraffe
{

    public static $jsVersion = '1.1';
    /**
     * @var bool
     */
    public static $RUNNING_HANDLER = false;

    /**
     * @var array
     */
    protected static $data;

    /**
     * @var string
     */
    protected static $controllerName;

    /**
     * @var \ReflectionClass
     */
    protected static $controllerReflect;

    /**
     * @var mixed
     */
    protected static $controller;

    /**
     * @var string
     */
    protected static $method;
    /**
     * @var bool|null
     */
    protected static $initExecute;
    /**
     * @var \stdClass
     */
    protected static $additionalData;

    /**
     * @var \stdClass|null
     */
    protected static $handledAdditionalData;
    /**
     * @var string|null
     */
    public static $CONTROLLER_PREFIX;

    /**
     * @var string|null
     */
    public static $METHOD_PREFIX;

    /**
     * @var string|null
     */
    public static $VIEW_PREFIX;

    /**
     * @var string|null
     */
    public static $DEVELOPER_EMAILS;

    /**
     * @var string|null
     */
    public static $PROJECT = null;
    /**
     * @var string
     */
    public static $ENVIRONMENT = 'production';

    /**
     * @var null|string
     */
    public static $JS_DIR = null;

    /**
     * Giraffe constructor.
     * @param array $allowedControllers
     * @param string|null $controllerPrefix
     * @param string|null $methodPrefix
     * @throws \Exception
     */
    public function __construct($allowedControllers = [], $controllerPrefix = null, $methodPrefix = null, $viewPrefix = null)
    {
        static::initUpJS();
        if (static::$RUNNING_HANDLER == true) {
            // Don't allow Giraffe to run more than once.
            throw new \Exception('Giraffe is already handling a request.');
            die;
        }

        header('Content-Type: application/json');

        static::$RUNNING_HANDLER = true;

        static::$CONTROLLER_PREFIX = $controllerPrefix;
        static::$METHOD_PREFIX = $methodPrefix;
        static::$VIEW_PREFIX = $viewPrefix;
        static::$additionalData = new \stdClass();

        $json = static::getJsonData();
        if (is_object($json)) {
            static::$controllerName = $json->controller ?? null;
            static::$method = $json->method ?? null;
            static::$data = $json->data ?? null;
            static::$initExecute = $json->initExecute ?? null;
        }
        if ((static::$controllerName === null || static::$method === null) && !isset(static::$initExecute)) {
            // We don't have the required information.
            throw new \Exception('Missing Giraffe controller or method.');
            die;
        }

        if (static::$CONTROLLER_PREFIX !== null) {
            static::$controllerName = static::$CONTROLLER_PREFIX . static::$controllerName;
        }
        if (static::$METHOD_PREFIX !== null) {
            static::$method = static::$METHOD_PREFIX . static::$method;
        }
        if (isset(static::$initExecute)) {
            $response = static::initExecute();
        } else {
            $response = static::callControllerMethod($allowedControllers);
        }
        $jsonResponse = json_encode($response);

        echo $jsonResponse;
        die;
    }

    /**
     * @return string
     */
    public static function getJSFilename()
    {
        return 'giraffe.' . static::$jsVersion . '.js';
    }

    public static function initUpJS()
    {
        $giraffeJSpath = __DIR__ . '/../js/' . static::getJSFilename();
        $srcExists = file_exists($giraffeJSpath);

        if (static::getJSDIR() && $srcExists) {
            $giraffeWritePath = static::getJSDIR() . '/' . static::getJSFilename();
            if (!file_exists($giraffeWritePath)) {
                $giraffeJSData = file_get_contents($giraffeJSpath);
                file_put_contents($giraffeWritePath, $giraffeJSData);

            }
        }
    }

    private static function callControllerMethod($allowedControllers)
    {
        static::initController($allowedControllers);
        $controllerMethod = [static::$controller, static::$method];
        if (!(static::$controllerReflect->hasMethod(static::$method) && is_callable($controllerMethod))) {
            // The method either doesn't exist or is not callable.
            throw new \Exception('Invalid controller method.');
            die;
        }

        //pick up the return from the method
        $controllerResponse = call_user_func_array([static::$controller, static::$method], []);

        //pick up any static calls for admin notifications etc....
        $response = static::appendAdditionalData($controllerResponse);

        return $response;
    }

    /**
     * Appends notification information etc to the return data.
     * @param \stdClass $controllerResponse
     * @return \stdClass
     */
    private static function appendAdditionalData($controllerResponse)
    {
        if (isset(static::$handledAdditionalData)) {
            static::$additionalData = static::$handledAdditionalData;
        }
        $response = static::$additionalData;
        $response->response = $controllerResponse;
        return $response;
    }


    /**
     * @param array $allowedControllers
     * @throws \Exception
     */
    protected static function initController($allowedControllers)
    {
        if (in_array(static::$controllerName, $allowedControllers)) {
            if (!class_exists(static::$controllerName)) {
                throw new \Exception('Invalid controller.');
            }
            static::$controllerReflect = new \ReflectionClass(static::$controllerName);
            static::$controller = static::$controllerReflect->newInstance();
        } else {
            throw new \Exception('Non-permitted controller: ' . static::$controllerName);
        }
    }


    /**
     * @param null|string $key
     * @param null $default
     * @return null
     */
    public static function getData($key = null, $default = null)
    {
        $result = $default;
        if ($key !== null && is_object(static::$data) && isset(static::$data->{$key})) {
            $result = static::$data->{$key};
        } elseif ($key === null) {
            $result = static::$data;
        }
        return $result;
    }


    /**
     * @param string $key
     * @param string $missingMessage
     * @return null
     */
    public static function getRequiredData($key, $missingMessage = 'Missing field: [field]')
    {
        $result = static::getData($key, null);
        if ($result === null || strlen($result) === 0) {
            $missingMessage = str_replace('[field]', $key, $missingMessage);
            header('Content-Type: application/json');
            echo json_encode(['error' => $missingMessage]);
            die;
        }
        return $result;
    }


    /**
     * @return mixed
     */
    protected static function getJsonData()
    {
        include_once(__DIR__ . '/../helper/helper.php');
        $jsonText = file_get_contents('php://input');
        $data = json_decode($jsonText);
        $headers = getallheaders();
        if (!(is_object($data) && isset($data->controller))) {
            foreach ($headers as $key => $value) {
                if ($key == 'giraffe-json') {
                    $data = json_decode($value);
                }
            }
        }

        return $data;
    }


    /**
     * @return string
     */
    public static function getControllerName()
    {
        return static::$controllerName;
    }


    /**
     * @return string
     */
    public static function getMethod()
    {
        return static::$method;
    }


    /**
     * @param string $message
     * @param null $type
     * @param null $timeout
     * @param null $delay
     */
    public static function notification($message, $type = null, $timeout = null, $delay = null)
    {
        static::prepareAdditionalData();
        if (!isset(static::$additionalData->notifications)) {
            static::$additionalData->notifications = [];
        }
        $notification = new \stdClass();
        $notification->message = $message;
        $notification->type = $type;
        $notification->timeout = $timeout;
        $notification->delay = $delay;
        static::$additionalData->notifications[] = $notification;
    }

    public static function modal($header = '', $body = '')
    {
        static::prepareAdditionalData();
        $modal = new \stdClass();
        $modal->header = strip_tags($header, 'span');
        $modal->body = $body;
        static::$additionalData->modal = $modal;
    }

    public static function clearNotifications()
    {
        static::$additionalData->clearNotifications = true;
    }

    /**
     * @param $errorData
     * @return bool
     */
    public static function formErrors($errorData)
    {
        static::prepareAdditionalData();
        if (!is_object($errorData)) {
            return false;
        }
        static::$additionalData->formErrors = $errorData;

    }

    /**
     * @param string $location
     * @param null|int $delay
     */
    public static function redirect($location, $delay = null)
    {
        static::prepareAdditionalData();
        $redirect = new \stdClass();
        $redirect->location = $location;
        $redirect->delay = $delay;
        static::$additionalData->redirect = $redirect;
    }


    /**
     * @param null|int $delay
     * @param bool $forceReload
     */
    public static function refresh($delay = null, $forceReload = false)
    {
        static::prepareAdditionalData();
        $refresh = new \stdClass();
        $refresh->delay = $delay;
        $refresh->force = $forceReload;
        static::$additionalData->refresh = $refresh;
    }


    /**
     * @param string $name
     * @param array $arguments
     * @param null|int $delay
     */
    public static function javascriptFunction($name, $arguments = [], $delay = null)
    {
        static::prepareAdditionalData();
        if (!isset(static::$additionalData->functions)) {
            static::$additionalData->functions = [];
        }
        $function = new \stdClass();
        $function->name = $name;
        $function->arguments = $arguments;
        $function->delay = $delay;
        static::$additionalData->functions[] = $function;
    }

    public static function getDataTableSearchValue()
    {
        $search = static::getData('search');
        if (isset($search->value) && !empty(trim($search->value))) {
            return $search->value;
        }

        return false;
    }

    public static function getDataTableSearchRegex()
    {
        $search = static::getData('search');
        if (isset($search->regex) && !empty(trim($search->regex))) {
            return $search->regex;
        }

        return false;
    }

    public static function getDataTablesStart()
    {
        $start = static::getData('start');
        if (isset($start) && is_numeric($start)) {
            return $start;
        }

        return 0;
    }

    public static function getDataTablesLength()
    {
        $length = static::getData('length');
        if (isset($length) && is_numeric($length)) {
            return $length;
        }

        return 0;
    }

    public static function getDataTablesOrderDirection()
    {
        $order = static::getData('order');
        if (isset($order->dir)) {
            return $order->dir;
        }

        return 'asc';
    }

    public static function getDataTablesOrderColumn()
    {
        $order = static::getData('order');
        if (isset($order->column)) {
            return $order->column;
        }

        return 0;
    }


    public static function getDataTablePage()
    {
        return floor(static::getDataTableOffset() / static::getDataTableLimit()) + 1;
    }


    public static function getDataTableLimit()
    {
        $limit = Giraffe::getData('length', 10);
        return $limit;
    }


    public static function getDataTableOffset()
    {
        $offset = Giraffe::getData('start', 0);
        return $offset;
    }


    public static function getDataTableSearch()
    {
        $searchObject = Giraffe::getData('search');
        if ($searchObject === null) {
            $searchObject = new \stdClass();
            $searchObject->value = '';
            $searchObject->regex = false;
        }
        if (isset($searchObject->value) && strlen($searchObject->value) > 0) {
            return $searchObject;
        } else {
            return null;
        }
    }


    public static function getDataTableOrderBy($fieldsOrder = null)
    {
        $columns = static::getData('columns', []);
        $order = static::getData('order', []);

        $result = null;

        if (is_array($order) && count($order) > 0) {
            foreach ($order as $o) {
                $column = $columns[$o->column];

                if (!$column->orderable) {
                    continue;
                }

                $orderByString = "{$column->data} {$o->dir}";

                if (is_object($fieldsOrder)) {
                    $methodName = "{$column->data}_orderBy";
                    if (method_exists($fieldsOrder, $methodName) && is_callable([$fieldsOrder, $methodName])) {
                        $orderByString = call_user_func_array([$fieldsOrder, $methodName], [$o->dir]);
                    }
                }

                if (is_string($orderByString) && strlen($orderByString) > 0) {
                    if ($result === null) {
                        $result = '';
                    }
                    $result .= ", {$orderByString}";
                }
            }
        }

        if (is_string($result) && strlen($result) > 0) {
            $result = trim($result, ', ');
        }

        return $result;
    }


    /**
     * @param $dbDataArray
     * @param int $totalRecords
     * @param null $fieldsHtml
     * @return \stdClass
     */
    public static function dataTableData($dbDataArray, $totalRecords = 0, $fieldsHtml = null)
    {
        $draw = static::getData('draw', 1);

        $columns = static::getData('columns', []);
        $fieldsOrderArray = [];
        foreach ($columns as $col) {
            $fieldsOrderArray[] = $col->data;
        }

        $returnData = [];
        $i = 0;
        if (is_array($dbDataArray) && count($dbDataArray) > 0) {
            foreach ($dbDataArray as $dataSet) {
                if (!isset($returnData[$i])) {
                    $returnData[$i] = [];
                }

                if ($fieldsOrderArray == null) {
                    foreach ($dataSet as $dataFieldKey => $dataValue) {
                        if (is_callable(array($fieldsHtml, $dataFieldKey))) {
                            $returnData[$i][$dataFieldKey] = $fieldsHtml->$dataFieldKey($dataValue, $dataSet);
                        } else {
                            $returnData[$i][$dataFieldKey] = $dataValue;
                        }
                    }
                } elseif (is_array($fieldsOrderArray)) {
                    foreach ($fieldsOrderArray as $fieldOrder) {
                        $found = false;
                        foreach ($dataSet as $dataFieldKey => $dataValue) {
                            if ($dataFieldKey == $fieldOrder) {
                                if (is_callable(array($fieldsHtml, $dataFieldKey))) {
                                    $returnData[$i][$dataFieldKey] = $fieldsHtml->$dataFieldKey($dataValue, $dataSet);
                                } else {
                                    $returnData[$i][$dataFieldKey] = $dataValue;
                                }
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            if (is_callable(array($fieldsHtml, $fieldOrder))) {
                                $returnData[$i][$fieldOrder] = $fieldsHtml->$fieldOrder('', $dataSet);
                            } else {
                                $returnData[$i][$fieldOrder] = '';
                            }
                        }
                    }
                } else {
                    $returnData[$i][] = '';
                }

                $i++;
            }
        }


        $returnObject = new \stdClass();

        $returnObject->data = $returnData;
        $returnObject->recordsTotal = $totalRecords;
        $returnObject->recordsFiltered = $totalRecords;
        $returnObject->draw = $draw;

        return $returnObject;
    }

    public static function insertView($htmlElement, $view, $data = null)
    {
        return static::getView($view, $data, $htmlElement);
    }

    public static function getView($view, $data = null, $htmlElement = null)
    {
        $prefix = isset(static::$VIEW_PREFIX) ? static::$VIEW_PREFIX : '';
        $view = str_replace('.php', '', $view);
        $path = $prefix . $view . '.php';

        if (file_exists($path)) {
            ob_start();
            if (is_array($data) && count($data)) {
                extract($data);
            }
            include($path);
            $output = ob_get_clean();
            if (isset($htmlElement)) {
                static::addView($htmlElement, $output);
            }
            return $output;
        }
        return false;
    }

    public static function addView($htmlElement, $output)
    {
        static::prepareAdditionalData();
        if (!isset(static::$additionalData->views)) {
            static::$additionalData->views = new \stdClass();
        }
        static::$additionalData->views->{$htmlElement} = $output;
    }

    private static function prepareAdditionalData()
    {
        if (!isset(static::$additionalData)) {
            static::$additionalData = new \stdClass();
        }
    }

    private static function initExecute()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $response = new \stdClass();

        $initExecutes = (
            isset($_SESSION['giraffeInitExecute']) &&
            is_array($_SESSION['giraffeInitExecute']) &&
            count($_SESSION['giraffeInitExecute'])
        ) ? $_SESSION['giraffeInitExecute'] : false;
        $executables = [];
        if ($initExecutes) {
            foreach ($initExecutes as $index => $additionalData) {
                if (isset($additionalData->next)) {
                    $_SESSION['giraffeInitExecute'][] = $additionalData->next;
                } else {
                    $executables[] = $additionalData;
                }
                if (isset($_SESSION['giraffeInitExecute'][$index])) {
                    unset($_SESSION['giraffeInitExecute'][$index]);
                }
                if (isset($initExecutes[$index])) {
                    unset($initExecutes[$index]);
                }
            }
        }
        $response->executables = $executables;
        $response->environment = static::$ENVIRONMENT;
        return $response;
    }

    public static function execute()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['giraffeInitExecute'])) {
            $_SESSION['giraffeInitExecute'] = [];
        }
        $_SESSION['giraffeInitExecute'][] = static::$additionalData;
        static::$additionalData = new \stdClass();
    }

    public static function executeNext()
    {
        if (static::$RUNNING_HANDLER) {
            return static::execute();
        }
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['giraffeInitExecute'])) {
            $_SESSION['giraffeInitExecute'] = [];
        }
        $additionalData = new \stdClass();
        $additionalData->next = static::$additionalData;
        $_SESSION['giraffeInitExecute'][] = $additionalData;
        static::$additionalData = new \stdClass();
    }

    public static function clear()
    {
        if (!isset(static::$handledAdditionalData)) {
            static::$handledAdditionalData = static::$additionalData;
        }
        static::$additionalData = new \stdClass();
    }

    /**
     * @param string|callable|null $content
     * @param string|null $email
     * @param string|null $subject
     */
    public static function obMail($content = null, $email = null, $subject = null)
    {
        $email = $email??static::getDeveloperEmails();
        $subject = $subject??static::getProject() . ' Debugging';

        if (is_callable($content)) {
            ob_start();
            $content();
            $c = ob_get_clean();
        } elseif (!is_string($content)) {
            $c = ob_get_clean();
        }
        @mail($email, $subject, $c);
    }

    /**
     * @param string $developerEmails
     */
    public static function setDeveloperEmails(string $developerEmails)
    {
        static::$DEVELOPER_EMAILS = $developerEmails;
    }

    /**
     * @return null|string
     */
    public static function getDeveloperEmails()
    {
        return static::$DEVELOPER_EMAILS??'';
    }

    /**
     * @param array|\stdClass $x
     * @return bool
     */
    public static function canIterate($x)
    {
        if (is_array($x) && count($x) > 0) {
            return true;
        }
        if (is_object($x)) {

            foreach ($x as $y) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return null|string
     */
    public static function getProject()
    {
        return self::$PROJECT??'';
    }

    /**
     * @param null|string $PROJECT
     */
    public static function setProject(string $PROJECT)
    {
        self::$PROJECT = $PROJECT;

    }

    /**
     * @param string $ENVIRONMENT
     */
    public static function setEnvironment(string $ENVIRONMENT)
    {
        self::$ENVIRONMENT = $ENVIRONMENT;
    }

    /**
     * @return null
     */
    public static function getJSDIR()
    {
        if ((!isset(static::$JS_DIR)) || static::$JS_DIR === null) {
            return false;
        }
        return rtrim(static::$JS_DIR, '/');
    }

    /**
     * @param $JS_DIR
     */
    public static function setJSDIR($JS_DIR)
    {
        static::$JS_DIR = $JS_DIR;
        if (!file_exists(static::getJSDIR() . '/' . static::getJSFilename())) {
            static::initUpJS();
        }

    }

    /**
     * @param $array
     * @return mixed
     * @throws \Exception
     */
    public static function declareArray (& $array) {


        $args = func_get_args();
        if (count($args) === 1) {
            return $args[0];
        }
        array_shift($args); // Strip the array out of the arguments
        $targetArray =& $array;
        if (is_array($args) && count($args) > 0) {
            foreach ($args as $arg) {
                if (! (is_string($arg) || is_numeric($arg))) {
                    throw new \Exception('All arguments passed to declare_array() must be a string or be numeric.. apart for the first argument which must be an array.');
                }
                if (! array_key_exists($arg, $targetArray)) {
                    $targetArray[$arg] = array();
                }
                if (! is_array($targetArray[$arg])) {
                    throw new \Exception('All existing/created elements used in declare_array() should be of type array.');
                }
                $targetArray =& $targetArray[$arg];
            }
        }
    }


}