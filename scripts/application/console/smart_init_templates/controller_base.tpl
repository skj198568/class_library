/**
 * Created by PhpStorm.
 * User: SmartInit
 */

namespace app\api\base;

use app\api\controller\ApiController;
use app\index\model\{$table_name}Model;
use ClassLibrary\ClFieldVerify;
use ClassLibrary\ClArray;

/**
 * {$table_comment['name']} Base
 * Class {$table_name} Base Api
 * @package app\api\base
 */
class {$table_name}BaseApiController extends ApiController
{

    /**
     * 列表
     * @return \think\response\Json|\think\response\Jsonp
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $where = [];
        return $this->ar(1, $this->paging({$table_name}Model::instance(), $where, function ($items) {
            //拼接额外字段 & 格式化相关字段
            return {$table_name}Model::showFormat({$table_name}Model::showMapFields($items));
        }), '{$ar_get_list_json}');
    }

    /**
     * 单个信息
     * @return \think\response\Json|\think\response\Jsonp
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get()
    {
        $id = get_param({$table_name}Model::F_ID, ClFieldVerify::instance()->verifyNumber()->fetchVerifies(), '主键id或id数组');
        $info = {$table_name}Model::getById($id);
        //拼接额外字段 & 格式化相关字段
        $info = {$table_name}Model::showFormat({$table_name}Model::showMapFields($info));
        return $this->ar(1, ['info' => $info], '{$ar_get_json}');
    }

    /**
     * 创建
     */
    public function create()
    {
        $fields = ClArray::getByKeys(input(), {$table_name}Model::getAllFields());
        //创建
        {$table_name}Model::instance()->insert($fields);
        return $this->ar(1, ['id' => {$table_name}Model::instance()->getLastInsID()], '{
    "status" : "api-{$table_name}-create-1",
    "id" : "主键id"
}');
    }

    /**
     * 更新
     */
    public function update()
    {
        $id = get_param({$table_name}Model::F_ID, ClFieldVerify::instance()->verifyNumber()->fetchVerifies(), '主键id或id数组');
        $fields = ClArray::getByKeys(input(), {$table_name}Model::getAllFields());
        //更新
        {$table_name}Model::instance()->where([
            {$table_name}Model::F_ID => $id
        ])->setField($fields);
        return $this->ar(1, ['id' => $id], '{
    "status" : "api-{$table_name}-update-1",
    "id" : "主键id"
}');
    }

    /**
     * 删除
     * @return \think\response\Json|\think\response\Jsonp
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete()
    {
        $id = get_param({$table_name}Model::F_ID, ClFieldVerify::instance()->verifyNumber()->fetchVerifies(), '主键id或id数组');
        //删除
        {$table_name}Model::instance()->where([
            {$table_name}Model::F_ID => is_array($id) ? ['in', $id] : $id
        ])->delete();
        return $this->ar(1, ['id' => $id], '{
    "status" : "api-{$table_name}-delete-1",
    "id" : "主键id"
}');
    }

}