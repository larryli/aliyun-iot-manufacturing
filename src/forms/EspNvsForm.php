<?php

namespace app\forms;

use app\Html;
use app\models\Device;
use app\models\DeviceQuery;
use Yii;
use ZipArchive;

/**
 * EspNvsForm
 */
class EspNvsForm extends DownloadForm
{
    /**
     * @var string
     */
    public $namespace;
    /**
     * @var string
     */
    public $nvsFilename;
    /**
     * @var string
     */
    public $nvsFileType;
    /**
     * @var int
     */
    public $nvsFileSize;
    /**
     * @var string[]
     */
    static public $nvsFilenames = [
        'deviceName' => 'DeviceName',
        'serialNo' => 'SerialNo',
    ];
    /**
     * @var string[]
     */
    static public $nvsFileTypes = [
        'bin' => '二进制',
        'csv' => 'CSV',
        'csvBin' => 'CSV + 二进制',
    ];
    /**
     * @var array The table of permutations for substituting CRC32 data with
     */
    protected static $crc32table = array(
        0x00000000, 0x77073096, 0xEE0E612C, 0x990951BA, 0x076DC419, 0x706AF48F,
        0xE963A535, 0x9E6495A3, 0x0EDB8832, 0x79DCB8A4, 0xE0D5E91E, 0x97D2D988,
        0x09B64C2B, 0x7EB17CBD, 0xE7B82D07, 0x90BF1D91, 0x1DB71064, 0x6AB020F2,
        0xF3B97148, 0x84BE41DE, 0x1ADAD47D, 0x6DDDE4EB, 0xF4D4B551, 0x83D385C7,
        0x136C9856, 0x646BA8C0, 0xFD62F97A, 0x8A65C9EC, 0x14015C4F, 0x63066CD9,
        0xFA0F3D63, 0x8D080DF5, 0x3B6E20C8, 0x4C69105E, 0xD56041E4, 0xA2677172,
        0x3C03E4D1, 0x4B04D447, 0xD20D85FD, 0xA50AB56B, 0x35B5A8FA, 0x42B2986C,
        0xDBBBC9D6, 0xACBCF940, 0x32D86CE3, 0x45DF5C75, 0xDCD60DCF, 0xABD13D59,
        0x26D930AC, 0x51DE003A, 0xC8D75180, 0xBFD06116, 0x21B4F4B5, 0x56B3C423,
        0xCFBA9599, 0xB8BDA50F, 0x2802B89E, 0x5F058808, 0xC60CD9B2, 0xB10BE924,
        0x2F6F7C87, 0x58684C11, 0xC1611DAB, 0xB6662D3D, 0x76DC4190, 0x01DB7106,
        0x98D220BC, 0xEFD5102A, 0x71B18589, 0x06B6B51F, 0x9FBFE4A5, 0xE8B8D433,
        0x7807C9A2, 0x0F00F934, 0x9609A88E, 0xE10E9818, 0x7F6A0DBB, 0x086D3D2D,
        0x91646C97, 0xE6635C01, 0x6B6B51F4, 0x1C6C6162, 0x856530D8, 0xF262004E,
        0x6C0695ED, 0x1B01A57B, 0x8208F4C1, 0xF50FC457, 0x65B0D9C6, 0x12B7E950,
        0x8BBEB8EA, 0xFCB9887C, 0x62DD1DDF, 0x15DA2D49, 0x8CD37CF3, 0xFBD44C65,
        0x4DB26158, 0x3AB551CE, 0xA3BC0074, 0xD4BB30E2, 0x4ADFA541, 0x3DD895D7,
        0xA4D1C46D, 0xD3D6F4FB, 0x4369E96A, 0x346ED9FC, 0xAD678846, 0xDA60B8D0,
        0x44042D73, 0x33031DE5, 0xAA0A4C5F, 0xDD0D7CC9, 0x5005713C, 0x270241AA,
        0xBE0B1010, 0xC90C2086, 0x5768B525, 0x206F85B3, 0xB966D409, 0xCE61E49F,
        0x5EDEF90E, 0x29D9C998, 0xB0D09822, 0xC7D7A8B4, 0x59B33D17, 0x2EB40D81,
        0xB7BD5C3B, 0xC0BA6CAD, 0xEDB88320, 0x9ABFB3B6, 0x03B6E20C, 0x74B1D29A,
        0xEAD54739, 0x9DD277AF, 0x04DB2615, 0x73DC1683, 0xE3630B12, 0x94643B84,
        0x0D6D6A3E, 0x7A6A5AA8, 0xE40ECF0B, 0x9309FF9D, 0x0A00AE27, 0x7D079EB1,
        0xF00F9344, 0x8708A3D2, 0x1E01F268, 0x6906C2FE, 0xF762575D, 0x806567CB,
        0x196C3671, 0x6E6B06E7, 0xFED41B76, 0x89D32BE0, 0x10DA7A5A, 0x67DD4ACC,
        0xF9B9DF6F, 0x8EBEEFF9, 0x17B7BE43, 0x60B08ED5, 0xD6D6A3E8, 0xA1D1937E,
        0x38D8C2C4, 0x4FDFF252, 0xD1BB67F1, 0xA6BC5767, 0x3FB506DD, 0x48B2364B,
        0xD80D2BDA, 0xAF0A1B4C, 0x36034AF6, 0x41047A60, 0xDF60EFC3, 0xA867DF55,
        0x316E8EEF, 0x4669BE79, 0xCB61B38C, 0xBC66831A, 0x256FD2A0, 0x5268E236,
        0xCC0C7795, 0xBB0B4703, 0x220216B9, 0x5505262F, 0xC5BA3BBE, 0xB2BD0B28,
        0x2BB45A92, 0x5CB36A04, 0xC2D7FFA7, 0xB5D0CF31, 0x2CD99E8B, 0x5BDEAE1D,
        0x9B64C2B0, 0xEC63F226, 0x756AA39C, 0x026D930A, 0x9C0906A9, 0xEB0E363F,
        0x72076785, 0x05005713, 0x95BF4A82, 0xE2B87A14, 0x7BB12BAE, 0x0CB61B38,
        0x92D28E9B, 0xE5D5BE0D, 0x7CDCEFB7, 0x0BDBDF21, 0x86D3D2D4, 0xF1D4E242,
        0x68DDB3F8, 0x1FDA836E, 0x81BE16CD, 0xF6B9265B, 0x6FB077E1, 0x18B74777,
        0x88085AE6, 0xFF0F6A70, 0x66063BCA, 0x11010B5C, 0x8F659EFF, 0xF862AE69,
        0x616BFFD3, 0x166CCF45, 0xA00AE278, 0xD70DD2EE, 0x4E048354, 0x3903B3C2,
        0xA7672661, 0xD06016F7, 0x4969474D, 0x3E6E77DB, 0xAED16A4A, 0xD9D65ADC,
        0x40DF0B66, 0x37D83BF0, 0xA9BCAE53, 0xDEBB9EC5, 0x47B2CF7F, 0x30B5FFE9,
        0xBDBDF21C, 0xCABAC28A, 0x53B39330, 0x24B4A3A6, 0xBAD03605, 0xCDD70693,
        0x54DE5729, 0x23D967BF, 0xB3667A2E, 0xC4614AB8, 0x5D681B02, 0x2A6F2B94,
        0xB40BBE37, 0xC30C8EA1, 0x5A05DF1B, 0x2D02EF8D,
    );

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if ($this->nvsFilename === null) {
            $this->nvsFilename = 'serialNo';
        }
        if ($this->nvsFileType === null) {
            $this->nvsFileType = 'csvBin';
        }
        if ($this->nvsFileSize === null) {
            $this->nvsFileSize = 24576;
        }
        if ($this->namespace === null) {
            $this->namespace = 'AliyunIoT';
        }
        if ($this->serialNoKeyName === null) {
            $this->serialNoKeyName = 'SerialNo';
        }
        if ($this->productKeyKeyName === null) {
            $this->productKeyKeyName = 'ProductKey';
        }
        if ($this->deviceNameKeyName === null) {
            $this->deviceNameKeyName = 'DeviceName';
        }
        if ($this->deviceSecretKeyName === null) {
            $this->deviceSecretKeyName = 'DeviceSecret';
        }
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'productKey' => '产品',
            'nvsFilename' => 'NVS 文件名',
            'nvsFileType' => 'NVS 文件类型',
            'nvsFileSize' => 'NVS 二进制文件大小',
            'namespace' => 'NVS 命名空间',
            'serialNoKeyName' => 'SerialNo 键名',
            'productKeyKeyName' => 'ProductKey 键名',
            'deviceNameKeyName' => 'DeviceName 键名',
            'deviceSecretKeyName' => 'DeviceSecret 键名',
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        $when = function ($model) {
            /** @var $model static */
            return $model->nvsFileType != 'csv';
        };
        $prefixId = Html::getInputId($this, 'dbPrefix');
        $whenClient = "function (attribute, value) { return $('#{$prefixId}').val() != 'csv' }";
        $filter = function ($value) {
            return intval(ceil($value / 4096) * 4096);
        };
        return [
            [['productKey', 'nvsFilename', 'namespace', 'productKeyKeyName', 'deviceNameKeyName', 'deviceSecretKeyName'], 'required'],
            [['productKey', 'namespace', 'serialNoKeyName', 'productKeyKeyName', 'deviceNameKeyName', 'deviceSecretKeyName'], 'trim'],
            ['productKey', 'in', 'range' => array_keys($this->products)],
            ['nvsFilename', 'in', 'range' => array_keys(static::$nvsFilenames)],
            ['nvsFileType', 'in', 'range' => array_keys(static::$nvsFileTypes)],
            ['nvsFileSize', 'integer', 'min' => 12288, 'max' => 65536, 'when' => $when, 'whenClient' => $whenClient],
            ['nvsFileSize', 'filter', 'filter' => $filter, 'when' => $when],
            [['namespace', 'serialNoKeyName', 'productKeyKeyName', 'deviceNameKeyName', 'deviceSecretKeyName'], 'string', 'max' => '15', 'encoding' => 'ASCII'],
            [['productKeyKeyName', 'deviceNameKeyName', 'deviceSecretKeyName'], 'compare', 'compareAttribute' => 'serialNoKeyName', 'operator' => '!='],
            [['deviceNameKeyName', 'deviceSecretKeyName'], 'compare', 'compareAttribute' => 'productKeyKeyName', 'operator' => '!='],
            [['deviceSecretKeyName'], 'compare', 'compareAttribute' => 'deviceNameKeyName', 'operator' => '!='],
        ];
    }

    /**
     * @return DeviceQuery
     */
    protected function findDevice()
    {
        return Device::find()->new();
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function generateFile(&$filename)
    {
        $filename = $this->productKey . date('-Ymd-His') . '.zip';
        $file = Yii::getAlias('@runtime/' . $filename);
        $zip = new ZipArchive;
        if (!$zip->open($file, ZipArchive::CREATE)) {
            $this->addError('productKey', '创建临时压缩文件发生错误。');
            return '';
        }
        $applies = [];
        foreach (Device::find()->new()->productKey($this->productKey)->each() as $model) {
            /** @var Device $model */
            $name = $this->nvsFilename == 'serialNo' ? $model->serial_no : $model->device_name;
            switch ($this->nvsFileType) {
                case 'bin':
                    $zip->addFromString($name . '.bin', $this->generateBinary($model));
                    break;
                case 'csv':
                    $zip->addFromString($name . '.csv', $this->generateCsv($model));
                    break;
                case 'csvBin':
                    $zip->addFromString($name . '.csv', $this->generateCsv($model));
                    $zip->addFromString($name . '.bin', $this->generateBinary($model));
                    break;
            }
            $applies[] = $model->apply_id;
        }
        $applies = array_unique($applies);
        $zip->close();
        $content = file_get_contents($file);
        unlink($file);
        Device::updateAll(['state' => Device::STATE_SUCCESS], ['apply_id' => $applies, 'state' => Device::STATE_NEW]);
        return $content;
    }

    /**
     * @param Device $model
     * @return string
     */
    protected function generateCsv($model)
    {
        $content = "key,type,encoding,value\n{$this->namespace},namespace,,\n";
        if (!empty($this->serialNoKeyName)) {
            $content .= "{$this->serialNoKeyName},data,string,{$model->serial_no}\n";
        }
        $content .= "{$this->productKeyKeyName},data,string,{$this->productKey}\n";
        $content .= "{$this->deviceNameKeyName},data,string,{$model->device_name}\n";
        $content .= "{$this->deviceSecretKeyName},data,string,{$model->device_secret}\n";
        return $content;
    }

    /**
     * @param Device $model
     * @return string
     */
    protected function generateBinary($model)
    {
        $pages = intval($this->nvsFileSize / 4096) - 1; // trim last empty page
        $data = $this->generateBinaryEntry(0, 0x01, $this->namespace, 1);
        if (!empty($this->serialNoKeyName)) {
            $data .= $this->generateBinaryEntry(1, 0x21, $this->serialNoKeyName, $model->serial_no);
        }
        $data .= $this->generateBinaryEntry(1, 0x21, $this->productKeyKeyName, $model->productKey);
        $data .= $this->generateBinaryEntry(1, 0x21, $this->deviceNameKeyName, $model->device_name);
        $data .= $this->generateBinaryEntry(1, 0x21, $this->deviceSecretKeyName, $model->device_secret);
        $data = str_split(bin2hex($data), 8064); // (4096 - 64) * 2
        if (count($data) > $pages) {
            $this->addError('nvsFileSize', '文件大小太小，不能存放所有数据。');
            return '';
        }
        $content = '';
        for ($page = 0; $page < $pages; $page++) {
            $content .= $this->generateBinaryHeader($page);
            if (isset($data[$page])) {
                $len = strlen($data[$page]) / 2;
                // Entry state bitmap
                $entries = intval($len / 32);
                $content .= str_repeat(chr(0xAA), intval(floor($entries / 4))); // 10101010
                switch ($entries % 4) {
                    case 1:
                        $content .= chr(0xFE); // 11111110
                        break;
                    case 2:
                        $content .= chr(0xFA); // 11111010
                        break;
                    case 3:
                        $content .= chr(0xEA); // 11101010
                        break;
                }
                $content .= str_repeat(chr(0xFF), 32 - intval(ceil($entries / 4))); // Empty bitmap
                $content .= hex2bin($data[$page]);
                $content .= str_repeat(chr(0xFF), 4032 - $len); // Empty Entries
            } else {
                $content .= str_repeat(chr(0xFF), 4064); // Empty Entries
            }
        }
        $content .= str_repeat(chr(0xFF), 4096); // Empty Page
        return $content;
    }

    /**
     * @param int $seqNo
     * @param int $version
     * @param int $state
     * @return string
     */
    protected function generateBinaryHeader($seqNo, $version = 0xFE, $state = 0xFFFFFFFE)
    {
        $content = pack('VC', $seqNo, $version);
        $content .= str_repeat(chr(0xFF), 19);
        $content .= pack('V', $this->crc32($content));
        return pack('V', $state) . $content;
    }

    protected function generateBinaryEntry($ns, $type, $key, $data)
    {
        $span = 1;
        $extra = '';
        switch ($type) {
            case 0x01: // namespace
                $data = pack('C', $data);
                $data .= str_repeat(chr(0xFF), 7);
                break;
            case 0x21: // string
                $len = strlen($data) + 1; // NULL
                $span += intval(ceil($len / 32));
                $extra = pack('Z*', $data);
                $data = pack('v', $len);
                $data .= str_repeat(chr(0xFF), 2);
                $data .= pack('V', $this->crc32($extra));
                $extra .= str_repeat(chr(0xFF), ($span - 1) * 32 - $len);
                break;
            default:
                $data = str_repeat(chr(0xFF), 8); // not support
                break;
        }
        $header = pack('C4', $ns, $type, $span, 0xFF);
        $data = pack('Z16', $key) . $data;
        return $header . pack('V', $this->crc32($header . $data)) . $data . $extra;
    }

    /**
     * Compute a CRC32 value of a string
     *
     * @param string $data The data to calculate the crc32 for
     *
     * @return int The computed CRC32 value
     */
    protected static function crc32($data)
    {
        return static::crc32update(0xFFFFFFFF, $data);
    }

    /**
     * Updates an existing CRC32 value with a new byte (for a stream)
     *
     * @param int $crc32 The previous CRC32 value
     * @param string $data The new byte to end (string length 1)
     *
     * @return int The updated CRC32 value
     */
    protected static function crc32update($crc32, $data)
    {
        $result = $crc32 ^ 0xFFFFFFFF;
        $length = strlen($data);
        for ($i = 0; $i < $length; $i++) {
            $lowPart = $result & 0xFF;
            $shifted = ($result >> 8) & 0xFFFFFF;
            $result = static::$crc32table[($lowPart ^ ord($data[$i])) & 0xFF] ^ $shifted;
        }
        return $result ^ 0xFFFFFFFF;
    }
}
