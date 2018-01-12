<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 17:15
 */
namespace Tricolor\ZTracker\Core;

use Tricolor\ZTracker\Common\Util;
use Tricolor\ZTracker\Core\Enum\BinaryAnnotationType as Type;

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
        return new BinaryAnnotation($key, array(), Type::BOOL, Util::checkNotNull($endpoint, "endpoint"));
    }

    /**
     * @param $key
     * @param $value array<byte>|String
     * @param Type $type
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

    public function __construct($key, array $value, $type, Endpoint $endpoint)
    {
        $this->key = Util::checkNotNull($key, "key");
        $this->value = Util::checkNotNull($value, "value");
        $this->type = Util::checkNotNull($type, "type");
        $this->endpoint = $endpoint;
    }
}