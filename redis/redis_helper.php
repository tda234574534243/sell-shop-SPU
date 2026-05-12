<?php
class RedisHelper {
    private $r;
    public function __construct($host='127.0.0.1', $port=6379) {
        if (class_exists('Redis')) {
            $this->r = new Redis();
            try {
                $this->r->connect($host, $port);
            } catch (Exception $e) {
                $this->r = null;
            }
        } else {
            $this->r = null;
        }
    }

    public function set($key, $value, $ttl = 0) {
        if ($this->r) {
            if ($ttl > 0) return $this->r->setex($key, $ttl, $value);
            return $this->r->set($key, $value);
        }
        // fallback to file cache
        $file = sys_get_temp_dir() . '/redis_fallback_' . md5($key);
        $data = ['v' => $value, 'e' => $ttl>0 ? (time()+$ttl) : 0];
        return file_put_contents($file, json_encode($data)) !== false;
    }

    public function get($key) {
        if ($this->r) {
            $v = $this->r->get($key);
            return $v === false ? null : $v;
        }
        $file = sys_get_temp_dir() . '/redis_fallback_' . md5($key);
        if (!file_exists($file)) return null;
        $data = json_decode(file_get_contents($file), true);
        if (!$data) return null;
        if ($data['e'] > 0 && time() > $data['e']) { @unlink($file); return null; }
        return $data['v'];
    }

    public function del($key) {
        if ($this->r) return $this->r->del($key) > 0;
        $file = sys_get_temp_dir() . '/redis_fallback_' . md5($key);
        if (file_exists($file)) { unlink($file); return true; }
        return false;
    }
}

?>
