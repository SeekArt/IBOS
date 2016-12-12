<?php
/**
 * API 基本控制器
 *
 * @namespace application\modules\contact\controllers
 * @filename BaseApiController.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/10 13:38
 */


namespace application\core\controllers;

use application\core\utils\Ibos;
use application\modules\vote\utils\Validator;

/**
 * Class ApiController
 *
 * @package application\core\controllers
 */
class ApiController extends Controller
{
    /**
     * @var array 用户请求数据，默认只包含 $_GET + $_POST
     */
    protected $requestData = array();

    /**
     * @var array 请求参数验证规则
     */
    protected $validateRules = array();

    /**
     * @var array 请求参数过滤规则
     */
    protected $filterRules = array();

    /**
     * @var bool 是否需要登录，设置为 true 将检查用户是否登录
     */
    protected $needLogin = false;

    /**
     * 初始化方法
     */
    public function init()
    {
        parent::init();

        // 自定义异常
        $this->setExceptionHandler();

        // 设置登录验证
        if ($this->needLogin) {
            $this->checkLogin();
        }
    }

    /**
     * 设置异常处理方法
     */
    protected function setExceptionHandler()
    {
        set_exception_handler(array($this, 'handleException'));
    }

    /**
     * 登录验证：如果用户未登录，则返回相应的提示。
     *
     * @return bool
     */
    protected function checkLogin()
    {
        if (Ibos::app()->user->isGuest) {
            $msg = Ibos::lang('Need login', 'default');

            if (Ibos::app()->request->getIsAjaxRequest()) {
                return $this->ajaxBaseReturn(false, array(), $msg);
            } else {
                return $this->error($msg);
            }
        }

        return true;
    }

    /**
     * 重写 action 执行方法，在执行 action 前验证请求参数是否合法。
     *
     * @param string $actionID
     * @return void
     */
    public function run($actionID)
    {
        // 设置用户请求数据
        $this->setRequestData();

        // 验证请求是否符合改则
        $this->validateRequest($actionID);

        // 请求参数过滤
        $this->filterRequest($actionID);

        parent::run($actionID);
    }

    /**
     * 设置请求数据
     * 备注：如果是 raw data 需要自己重写该方法
     */
    protected function setRequestData()
    {
        $this->requestData = array_merge($_POST, $_GET);
    }

    /**
     * 验证请求数据是否符合规则
     *
     * @param string $actionId action 名称
     * @return bool
     * @throws \Exception
     */
    protected function validateRequest($actionId)
    {
        if (isset($this->validateRules[$actionId])) {
            $rule = $this->validateRules[$actionId];

            return Validator::validate($this->requestData, $rule);
        }

        return true;
    }

    /**
     * 根据过滤规则，规则请求数据
     * @todo 参数过滤方法暂未实现
     *
     * @param string $actionId action 名称
     * @return bool
     */
    protected function filterRequest($actionId)
    {
        if (isset($this->filterRules[$actionId])) {
            $rules = $this->filterRules[$actionId];

            return false;
        }

        return true;
    }

    /**
     * 自定义异常处理器
     *
     * @param \Exception $e
     * @return mixed
     */
    public function handleException($e)
    {
        $msg = $e->getMessage();

        if (Ibos::app()->request->getIsAjaxRequest()) {
            return $this->ajaxBaseReturn(false, array(), $msg);
        }

        return $this->error($msg, Ibos::app()->request->getUrlReferrer());
    }

    /**
     * 获取请求参数
     * 备注：如果有设置过滤规则，该方法比直接获取用户数据更加安全。
     *
     * @param string $requestName 参数名
     * @param null $default 参数默认值，默认为 null
     * @return mixed
     */
    public function getRequest($requestName, $default = null)
    {
        if (isset($this->requestData[$requestName])) {
            return $this->requestData[$requestName];
        }

        return $default;
    }
}
