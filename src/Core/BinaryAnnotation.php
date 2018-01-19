<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 17:15
 */
namespace Tricolor\ZTracker\Core;

use Tricolor\ZTracker\Common;

class BinaryAnnotation
{

    /**
     * Name used to lookup spans, such as {@link TraceKeys#HTTP_PATH "http.path"} or {@link
     * Constants#ERROR "error"}
     * String
     */
    public $key;
    /**
     * Serialized thrift bytes, in TBinaryProtocol format.
     *
     * <p>For legacy reasons, byte order is big-endian. See THRIFT-3217.
     * byte[]
     */
    public $value;
    /**
     * The thrift type of value, most often STRING.
     *
     * <p>Note: type shouldn't vary for the same key.
     * Type
     */
    public $type;

    /**
     * The host that recorded {@link #value}, allowing query by service name or address.
     *
     * <p>There are two exceptions: when {@link #key} is {@link Constants#CLIENT_ADDR} or {@link
     * Constants#SERVER_ADDR}, this is the source or destination of an RPC. This exception allows
     * zipkin to display network context of uninstrumented services, such as browsers or databases.
     * Endpoint
     */
    public $endpoint;

    public function __construct($key, $value, $type, Endpoint $endpoint)
    {
        $this->key = Common\Util::checkNotNull($key, "key");
        $this->value = Common\Util::checkNotNull($value, "value");
        $this->type = Common\Util::checkNotNull($type, "type");
        $this->endpoint = $endpoint;
    }

    /**
     * Special-cased form supporting {@link Constants#CLIENT_ADDR} and
     * {@link Constants#SERVER_ADDR}.
     *
     * @param key {@link Constants#CLIENT_ADDR} or {@link Constants#SERVER_ADDR}
     * @param endpoint Endpoint associated endpoint.
     * @return $this
     */
    public static function address($key, Endpoint $endpoint)
    {
        return new BinaryAnnotation($key, array(), BinaryAnnotationType::BOOL, Common\Util::checkNotNull($endpoint, "endpoint"));
    }

    /**
     * @param $key
     * @param $value String|boolean
     * @param $type int BinaryAnnotationType
     * @param Endpoint $endpoint
     * @return BinaryAnnotation
     */
    public static function create($key, $value, $type, Endpoint $endpoint)
    {
        if (is_string($value)) {
            $value = pack("H*", $value);
        }
        return new BinaryAnnotation($key, $value, $type, $endpoint);
    }

    /**
     * @param $o
     * @return bool
     */
    public function equals($o)
    {
        if ($o instanceof BinaryAnnotation) {
            return $o == $this;
        }
        if (is_array($o)) {
            return $o == get_object_vars($this);
        }
        return false;
    }

    /**
     * @return string
     */
    public function hashCode()
    {
        return spl_object_hash($this);
    }

    /**
     * @return array
     */
    public function convertToArray()
    {
        $array = array();
        foreach (get_object_vars($this) as $key => $val) {
            if ($val instanceof Endpoint) {
                $array[$key] = $val->convertToArray();
            } else {
                $array[$key] = $val;
            }
        }
        return $array;
    }

    /**
     * @param $vars
     * @return array
     */
    public static function shorten($vars)
    {
        if (!isset($vars)) return null;
        if (isset($vars['endpoint'])) {
            $vars['endpoint'] = Endpoint::shorten($vars['endpoint']);
        }
        return Common\Compress::map($vars, Common\Compress::BINARYANNOTATION_MAP);
    }

    /**
     * @param $shorten
     * @return array
     */
    public static function normalize($shorten)
    {
        $map = Common\Compress::BINARYANNOTATION_MAP;
        if (isset($shorten[$map['endpoint']])) {
            $shorten[$map['endpoint']] = Endpoint::normalize($shorten[$map['endpoint']]);
        }
        return Common\Compress::map($shorten, Common\Compress::MAP_BINARYANNOTATION);
    }
}