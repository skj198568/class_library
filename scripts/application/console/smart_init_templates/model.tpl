/**
 * Created by PhpStorm.
 * User: SmartInit
 * Date: {$date}
 * Time: {$time}
 */

namespace app\index\model;

use app\index\map\{$table_name}Map;

/**
 * {$table_comment['name']} Model
 */
class {$table_name}Model extends {$table_name}Map
{

    /**
     * 实例对象
     * @var null
     */
    private static $instance = null;

    /**
     * 实例对象
     * @return null|static
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 缓存清除触发器
     * @param $item
     */
    protected function cacheRemoveTrigger($item)
    {
        if(isset($item[self::F_ID])){
            self::getByIdRc($item[self::F_ID]);
        }
    }

}