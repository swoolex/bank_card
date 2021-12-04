<?php
/**
 * +----------------------------------------------------------------------
 * 国内银行卡归属银行解析
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace Swx\BankCard;


class BankCard {
    /**
     * 当前版本号
    */
    private $version = '1.0.1';
    /**
     * 失败原因
    */
    private $error = '';
    /**
     * 结果集
    */
    private $data = [];
    /**
     * 银行卡类型
    */
    private $CardType = [
        1 => '借记卡',
        2 => '贷记卡',
        3 => '准贷记卡',
        4 => '预付费卡',
    ];

    /**
     * 调用入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2021-12-03
     * @deprecated 暂不启用
     * @global 无
     * @param int $card 银行卡号
     * @return false.array
    */
    public function handle($card) {
        if (empty($card)) {
            $this->error = '银行卡号为空';
            return false;
        }
        $length = mb_strlen($card);
        if ($length > 19 || $length < 14) {
            $this->error = '银行卡号格式错误';
            return false;
        }
        $data = [];
        for ($i=4; $i <=10 ; $i++) {
            $key = substr($card, 0, $i);
            $arr = require __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR .$i. '.php';
            if (isset($arr[$key])) {
                $data = $arr[$key];
                break;
            }
        }
        if (empty($data)) {
            $this->error = '归属银行识别失败，建议通知SW-X开发组成员，更新归属地址库';
            return false;
        }

        $this->data = [
            'bank_name' => $data[0],
            'card_type_name' => $this->CardType[$data[2]],
            'card_type_name2' => $data[1],
            'card_type' => $data[2],
            'is_luhm' => $this->Luhm($card)
        ];

        return $this->data;
    }

    /**
     * 获取失败原因描述
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2021-12-03
     * @deprecated 暂不启用
     * @global 无
     * @return string
    */
    public function error() {
        return $this->error;
    }

    /**
     * 成员属性的方式读取结果集
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2021-12-03
     * @deprecated 暂不启用
     * @global 无
     * @param string $name
     * @return mixed
    */
    public function __get($name) {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return false;
    }
    
    /**
     * 从不含校验位的银行卡卡号采用 Luhm 校验算法获得校验位 16 OR 19位
     * 
     * @return
     */
    private function Luhm($cardId) {
        $arr_no = str_split($cardId);
        $last_n = $arr_no[count($arr_no)-1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n){
            if($i%2==0){
                $ix = $n*2;
                if($ix>=10){
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                }else{
                    $total += $ix;
                }
            }else{
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $total *= 9;
        if($last_n == ($total%10)){
            return true;
        }

        return false;
    }
}
