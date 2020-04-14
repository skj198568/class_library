<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/3
 * Time: 11:31
 */

namespace ClassLibrary;

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

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
    public static function getLetters($max_count) {
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
    public static function exportToCsv($titles, $values = []) {
        $file = DOCUMENT_ROOT_PATH . '/temp.csv';
        array_unshift($values, $titles);
        $f = fopen($file, 'w+');
        foreach ($values as $each_array) {
            fputcsv($f, $each_array);
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
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function exportToExcel($titles, $values = [], $suffix = 'xls', $is_delete = false) {
        $csv_file = self::exportToCsv($titles, $values);
        //转换为excel
        return self::csvToExcel($csv_file, $suffix, $is_delete);
    }

    /**
     * csv to excel
     * @param $csv_file
     * @param string $suffix
     * @param bool $is_delete
     * @return array|bool|string
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function csvToExcel($csv_file, $suffix = 'xls', $is_delete = false) {
        if (!is_file($csv_file)) {
            le_info(sprintf('文件“%s”，不存在。', $csv_file));
            return false;
        }
        $reader               = IOFactory::createReader('Csv')->setDelimiter(',')->setEnclosure('"')->setSheetIndex(0);
        $spreadsheet_from_csv = $reader->load($csv_file);
        $suffix               = strtolower($suffix);
        if ($suffix == 'xlsx') {
            $writer = IOFactory::createWriter($spreadsheet_from_csv, 'Xlsx');
        } else {
            $writer = IOFactory::createWriter($spreadsheet_from_csv, 'Xls');
        }
        $excel_file                         = explode('.', $csv_file);
        $excel_file[count($excel_file) - 1] = $suffix;
        $excel_file                         = implode('.', $excel_file);
        //保存excel
        $writer->save($excel_file);
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
     * @return array|mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function excelToArray($excel_file, $is_delete_excel = false, $is_delete_csv = false, $auto_fixed = false) {
        if (!is_file($excel_file)) {
            if (is_file(DOCUMENT_ROOT_PATH . $excel_file)) {
                $excel_file = DOCUMENT_ROOT_PATH . $excel_file;
            } else {
                le_info(sprintf('文件“%s”，不存在。', $excel_file));
                return false;
            }
        }
        $spreadsheet  = IOFactory::load($excel_file);
        $count        = $spreadsheet->getSheetCount();
        $return_array = [];
        //保存csv格式
        for ($sheet_index = 0; $sheet_index < $count; $sheet_index++) {
            $csv_file   = str_replace(ClFile::getSuffix($excel_file, true), sprintf('_%s.csv', $sheet_index), $excel_file);
            $obj_writer = IOFactory::createWriter($spreadsheet, 'Csv');
            //设置sheet
            $obj_writer->setSheetIndex($sheet_index);
            //保存sheet
            $obj_writer->save($csv_file);
            //csv格式化
            $csv_file = self::csvClear($csv_file);
            $return   = [];
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
                $return[] = self::csvLineToArray($content);
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

    /**
     * csv数据清理
     * @param $csv_absolute_file
     * @param bool $is_cover
     * @return mixed|string
     */
    public static function csvClear($csv_absolute_file, $is_cover = true) {
        if (!is_file($csv_absolute_file)) {
            if (is_file(DOCUMENT_ROOT_PATH . $csv_absolute_file)) {
                $csv_absolute_file = DOCUMENT_ROOT_PATH . $csv_absolute_file;
            } else {
                le_info(sprintf('文件“%s”，不存在。', $csv_absolute_file));
                return false;
            }
        }
        $new_csv_absolute_file = str_replace('.csv', '_temp.csv', $csv_absolute_file);
        $file_handle           = fopen($csv_absolute_file, 'r');
        $new_file_handle       = fopen($new_csv_absolute_file, 'w+');
        $titles                = [];
        $items                 = [];
        $is_first_put          = true;
        while (!feof($file_handle)) {
            $content = trim(fgets($file_handle));
            if (empty($content)) {
                continue;
            }
            while (strpos($content, ',"') !== false && substr_count($content, '"') % 2 != 0 && !feof($file_handle)) {
                //如果换行，则接着读取数据
                $content .= trim(fgets($file_handle));
            }
            $items = self::csvLineToArray($content);
            //处理标题
            if (empty($titles)) {
                $titles = $items;
            }
            //删除多余数据
            foreach ($items as $k => $v) {
                if ($k + 1 > count($titles)) {
                    unset($items[$k]);
                }
            }
            //判断空数据的行
            $temp_content_array = $items;
            for ($i = count($temp_content_array) - 1; $i >= 0; $i--) {
                if (empty(ClString::spaceTrim($temp_content_array[$i]))) {
                    unset($temp_content_array[$i]);
                }
            }
            if (!empty($temp_content_array)) {
                //处理数据回写
                array_walk($items, function (&$each_item) {
                    if (strpos($each_item, ',') !== false) {
                        $each_item = '"' . $each_item . '"';
                    }
                });
                $content = implode(',', $items);
                if ($is_first_put) {
                    $is_first_put = false;
                } else {
                    $content = "\n" . $content;
                }
                //写入新内容
                fputs($new_file_handle, $content);
            }
        }
        fclose($new_file_handle);
        fclose($file_handle);
        if ($is_cover) {
            //删除
            unlink($csv_absolute_file);
            //重命名
            rename($new_csv_absolute_file, $csv_absolute_file);
            return $csv_absolute_file;
        } else {
            return $new_csv_absolute_file;
        }
    }

    /**
     * csv行转数组
     * @param $line_content
     * @return array
     */
    public static function csvLineToArray($line_content) {
        $line_content = trim($line_content);
        //转码
        $line_content = ClString::encoding($line_content);
        $delimiter    = ',';
        $items        = explode(',', $line_content);
        $items_temp   = [];
        $item         = '';
        foreach ($items as $k => $v) {
            if (empty($item)) {
                $item = $v;
            } else {
                $item .= ',' . $v;
            }
            if (strpos($item, '"') === 0 && substr($item, -1, 1) !== '"') {
                continue;
            }
            //去除两端"
            $items_temp[] = trim(trim($item, '"'));
            //置空
            $item = '';
        }
        return $items_temp;
    }

    /**
     * csv自动填充合并的字段内容
     * @param $csv_absulute_url
     * @return array
     * @author SongKeJing
     * @date 2020/4/14 20:38
     */
    public static function csvAutoFixedColumn($csv_absulute_url) {
        $csv_absulute_url = self::csvClear($csv_absulute_url);
        $handle           = fopen($csv_absulute_url, 'r');
        $items            = [];
        while (!feof($handle)) {
            $items[] = self::csvLineToArray(fgets($handle));
        }
        foreach ($items as $k_son => $v_son) {
            $has_field = false;
            foreach ($v_son as $k_k_son => $v_v_son) {
                if (empty($v_v_son) && !$has_field) {
                    if (isset($items[$k_son - 1][$k_k_son]) && !empty($items[$k_son - 1][$k_k_son])) {
                        $items[$k_son][$k_k_son] = $items[$k_son - 1][$k_k_son];
                    }
                } else {
                    $has_field = true;
                }
            }
        }
        fclose($handle);
        return $items;
    }

}