<?php
/**
 * Created by PhpStorm.
 * User: SongKejing
 * QQ: 597481334
 * Date: 2017/8/30
 * Time: 18:22
 */

//namespace app\api\controller;


use app\index\model\BaseModel;
use ClassLibrary\ClCache;
use ClassLibrary\ClFieldVerify;
use think\Controller;

/**
 * 基础Api接口
 * Class ApiController
 * @package app\api\controller
 */
class ApiController extends Controller
{

    /**
     * 管理员id
     * @var int
     */
    protected $admin_id = 0;

    /**
     * 不校验的请求
     * @var array
     */
    private $uncheck_request = [];

    /**
     * 初始化函数
     */
    public function _initialize()
    {
        parent::_initialize();
        if (app_debug()) {
            log_info('$_REQUEST:', $_REQUEST);
        }
    }

    /**
     * 返回信息
     * @param int $code 返回码
     * @param array $data 返回的值
     * @param string $example 例子，用于自动生成api文档
     * @param bool $is_log
     * @return \think\response\Json|\think\response\Jsonp
     */
    protected function ar($code, $data = [], $example = '', $is_log = false)
    {
        $status = sprintf('%s-%s-%s-%s', request()->module(), request()->controller(), request()->action(), $code);
        return json_return(array_merge([
            'status' => $status,
        ], is_array($data) ? $data : [$data]), $is_log);
    }

    /**
     * 分页数据构建
     * @param $model_instance
     * @param $where
     * @param string $call_back 回调函数
     * @param int $limit 每页显示数
     * @param int $duration 缓存时间
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function paging(BaseModel $model_instance, $where, $call_back = '', $limit = PAGES_NUM, $duration = 0)
    {
        $limit = get_param('limit', ClFieldVerify::instance()->verifyIsRequire()->verifyNumber()->fetchVerifies(), '每页显示数量', $limit);
        $total = get_param('total', ClFieldVerify::instance()->verifyNumber()->fetchVerifies(), '总数，默认为0', 0);
        $page = get_param('page', ClFieldVerify::instance()->verifyIsRequire()->verifyNumber()->fetchVerifies(), '当前页码数', 1);
        $order = get_param('order', ClFieldVerify::instance()->verifyInArray(['asc', 'desc'])->fetchVerifies(), '排序， ["asc"， "desc"]任选其一，默认为"asc"', 'asc');
        $sort = get_param('sort', ClFieldVerify::instance()->verifyAlpha()->fetchVerifies(), '排序值，默认为表的主键', $model_instance->getPk());
        $return = [
            'limit' => $limit,
            'page' => $page,
            'total' => $total
        ];
        $return['rows'] = $model_instance
            ->cache(ClCache::getKey($model_instance->getTable(), $where, $order, $page, $limit, 'rows'), $duration)
            ->where($where)
            ->order([
                $sort => $order
            ])
            ->page($page)
            ->limit($limit)
            ->select();
        if (!empty($call_back) && gettype($call_back) == 'object') {
            $return['rows'] = $call_back($return['rows']);
        }
        if (empty($total)) {
            $return['total'] = $model_instance
                ->cache(ClCache::getKey($model_instance->getTable(), $where, $order, $page, $limit, 'total'), $duration)
                ->where($where)
                ->count();
        }
        return $return;
    }

}