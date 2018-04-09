<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/3
 * Time: 11:31
 */

namespace ClassLibrary;

/**
 * 实现原理是以csv为中间格式，进行excel转换
 * Class ClExcel(class library Excel)
 * @package Common\Common
 */
class ClExcel {

    /**
     * 03格式excel
     */
    const V_EXCEL_TYPE_XLS = 'xls';

    /**
     * 07格式excel
     */
    const V_EXCEL_TYPE_XLSX = 'xlsx';

    /**
     * 获取列数
     * @param integer $max_count 最大列数
     * @return array
     */
    public function getLetters($max_count) {
        $letter_str = ' ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $m          = ceil($max_count / 26);
        $r          = array();
        for ($i = 0; $i < $m; $i++) {
            for ($j = 1; $j < 27; $j++) {
                $r[] = trim($letter_str{$i} . $letter_str{$j});
                if (count($r) == $max_count) {
                    //退出循环
                    break;
                }
            }
        }
        unset($letter_str);
        unset($m);
        unset($max_count);
        return $r;
    }

    /**
     * 导出为csv
     * @param $titles
     * @param array $values
     * @return string
     */
    public function exportToCsv($titles, $values = []) {
        $file = DOCUMENT_ROOT_PATH . '/temp.csv';
        $f    = fopen($file, 'w+');
        //先put titles
        fputs($f, sprintf('"%s"' . "\n", implode('","', $titles)));
        //填充数据
        if (!empty($values)) {
            $values_string = '';
            foreach ($values as $k => $v) {
                $values_string .= sprintf('"%s"' . "\n", implode('","', $v));
            }
            //去除最后一行换行符
            $values_string = rtrim($values_string, "\n");
            //填充
            fputs($f, $values_string);
        }
        fclose($f);
        return $file;
    }

    /**
     * 导出数据为excel
     * @param $titles
     * @param array $values
     * @param string $suffix
     * @param bool $is_delete
     * @return array|bool|string
     * @throws \PHPExcel_Reader_Exception
     */
    public function exportToExcel($titles, $values = [], $suffix = 'xls', $is_delete = false) {
        $csv_file = $this->exportToCsv($titles, $values);
        //转换为excel
        return $this->csvToExcel($csv_file, $suffix, $is_delete);
    }

    /**
     * csv to excel
     * @param string $csv_file csv绝对地址
     * @param string $suffix 格式2003或2007
     * @param bool $is_delete 是否删除源文件
     * @return array|bool|string
     * @throws \PHPExcel_Reader_Exception
     */
    public function csvToExcel($csv_file, $suffix = 'xls', $is_delete = false) {
        if (!is_file($csv_file)) {
            le_info(sprintf('文件“%s”，不存在。', $csv_file));
            return false;
        }
        $object_csv    = new \PHPExcel_Reader_CSV();
        $csv           = $object_csv->load($csv_file);
        $object_writer = null;
        $suffix        = strtolower($suffix);
        if ($suffix == 'xlsx') {
            $object_writer = new \PHPExcel_Writer_Excel2007($csv);
        } else {
            $object_writer = new \PHPExcel_Writer_Excel5($csv);
        }
        $excel_file                         = explode('.', $csv_file);
        $excel_file[count($excel_file) - 1] = $suffix;
        $excel_file                         = implode('.', $excel_file);
        //保存excel
        $object_writer->save($excel_file);
        unset($object_writer);
        unset($object_csv);
        if ($is_delete) {
            //是否删除
            unlink($csv_file);
        }
        return $excel_file;
    }

    /**
     * excel to array
     * @param $excel_file
     * @param bool $is_delete_excel
     * @param bool $is_delete_csv
     * @param bool $auto_fixed
     * @return array|bool|mixed
     * @throws \PHPExcel_Writer_Exception
     */
    public function excelToArray($excel_file, $is_delete_excel = false, $is_delete_csv = false, $auto_fixed = false) {
        if (!is_file($excel_file)) {
            if (is_file(DOCUMENT_ROOT_PATH . $excel_file)) {
                $excel_file = DOCUMENT_ROOT_PATH . $excel_file;
            } else {
                le_info(sprintf('文件“%s”，不存在。', $excel_file));
                return false;
            }
        }
        $suffix     = ClFile::getSuffix($excel_file);
        $obj_reader = null;
        if ($suffix == 'xlsx') {
            $obj_reader = new \PHPExcel_Reader_Excel2007();
        } else {
            $obj_reader = new \PHPExcel_Reader_Excel5();
        }
        $excel        = $obj_reader->load($excel_file);
        $count        = $excel->getSheetCount();
        $obj_writer   = new \PHPExcel_Writer_CSV($excel);
        $return_array = [];
        //保存csv格式
        for ($sheet_index = 0; $sheet_index < $count; $sheet_index++) {
            $csv_file                       = explode('.', $excel_file);
            $csv_file[count($csv_file) - 1] = sprintf('_%s.csv', $sheet_index);
            $csv_file                       = implode('.', $csv_file);
            $obj_writer->setSheetIndex($sheet_index);
            $obj_writer->save($csv_file);
            $return = [];
            //标题
            $titles = [];
            //内容
            $item = [];
            //格式化数据
            $f_handle           = fopen($csv_file, 'r');
            $content            = '';
            $temp_content_array = [];
            while (!feof($f_handle)) {
                $content = trim(fgets($f_handle));
                if (empty($content)) {
                    continue;
                }
                $temp_content_array = ClString::toArray($content);
                while ($temp_content_array[count($temp_content_array) - 1] !== '"' && !feof($f_handle)) {
                    //如果换行，则接着读取数据
                    $content            .= trim(fgets($f_handle));
                    $temp_content_array = ClString::toArray($content);
                }
                $item = explode('","', $content);
                //去除两端"
                foreach ($item as $k => $v) {
                    $item[$k] = trim(trim($v, '"'));
                }
                //处理标题
                if (empty($titles)) {
                    $titles = $item;
                }
                //删除多余数据
                foreach ($item as $k => $v) {
                    if ($k + 1 > count($titles)) {
                        unset($item[$k]);
                    }
                }
                //判断空数据的行
                $temp_content_array = $item;
                for ($i = count($temp_content_array) - 1; $i >= 0; $i--) {
                    if (empty(ClString::spaceTrim($temp_content_array[$i]))) {
                        unset($temp_content_array[$i]);
                    }
                }
                if (!empty($temp_content_array)) {
                    $return[] = $item;
                }
            }
            fclose($f_handle);
            if ($is_delete_csv) {
                //删除csv
                unlink($csv_file);
            }
            $return_array[] = $return;
        }
        unset($obj_writer);
        unset($obj_reader);
        if ($is_delete_excel) {
            unlink($excel_file);
        }
        //处理显示问题多余空格问题
        $max_index = 0;
        foreach ($return_array as $k => $item) {
            $max_index = 0;
            //获取最大的index
            foreach ($item as $v_son) {
                foreach ($v_son as $k_k => $v_v_son) {
                    if (!empty($v_v_son) && $k_k > $max_index) {
                        $max_index = $k_k;
                    }
                }
            }
            //最大的index之外数据均删除
            foreach ($item as $k_son => $v_son) {
                foreach ($v_son as $k_k_son => $v_v_son) {
                    if ($k_k_son > $max_index) {
                        unset($return_array[$k][$k_son][$k_k_son]);
                    }
                }
            }
        }
        //处理标题归类问题
        if ($auto_fixed) {
            foreach ($return_array as $k => $item) {
                foreach ($item as $k_son => $v_son) {
                    $has_field = false;
                    foreach ($v_son as $k_k_son => $v_v_son) {
                        if (empty($v_v_son) && !$has_field) {
                            if (isset($return_array[$k][$k_son - 1][$k_k_son]) && !empty($return_array[$k][$k_son - 1][$k_k_son])) {
                                $return_array[$k][$k_son][$k_k_son] = $return_array[$k][$k_son - 1][$k_k_son];
                            }
                        } else {
                            $has_field = true;
                        }
                    }
                }
            }
        }
        return count($return_array) == 1 ? $return_array[0] : $return_array;
    }

}
