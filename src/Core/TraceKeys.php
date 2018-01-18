<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 17:18
 */
namespace Tricolor\ZTracker\Core;

/**
 * Well-known {@link BinaryAnnotation#key binary annotation keys}.
 *
 * <h3>Overhead of adding Trace Data</h3>
 *
 * Overhead is directly related to the size of trace data exported out of process. Accordingly, it
 * is better to tag what's important for latency troubleshooting, i.e. a whitelist vs. collecting
 * everything and filtering downstream. The keys listed here are very common in tracing tools, and
 * are considerate to the issue of overhead.
 *
 * <p> When evaluating new keys, consider how much additional data it implies, and if that data is
 * critical to classifying, filtering or displaying traces. More data often means larger systems,
 * less retention, or a lower sample rate.
 *
 * <p> For example, in zipkin, a thrift-encoded span with an "sr" annotation is 82 bytes plus the
 * size of its name and associated service. The maximum size of an HTTP cookie is 4096 bytes,
 * roughly 50x that. Even if compression helps, if you aren't analyzing based on cookies, storing
 * them displaces resources that could be used for more traces. Meanwhile, you have another system
 * storing private data! The takeaway isn't never store cookies, as there are valid cases for this.
 * The takeaway is to be conscious about what's you are storing.
 */
class TraceKeys
{
    /**
     * The domain portion of the URL or host header. Ex. "mybucket.s3.amazonaws.com"
     *
     * <p>Used to filter by host as opposed to ip address.
     */
    const HTTP_HOST = "http.host";

    /**
     * The HTTP method, or verb, such as "GET" or "POST".
     *
     * <p>Used to filter against an http route.
     */
    const HTTP_METHOD = "http.method";

    /**
     * The absolute http path, without any query parameters. Ex. "/objects/abcd-ff"
     *
     * Used to filter against an http route, portably with zipkin v1.
     *
     * <p>In zipkin v1, only equals filters are supported. Dropping query parameters makes the number
     * of distinct URIs less. For example, one can query for the same resource, regardless of signing
     * parameters encoded in the query line. This does not reduce cardinality to a HTTP single route.
     * For example, it is common to express a route as an http URI template like
     * /resource/{resource_id}. In systems where only equals queries are available, searching for
     * http/path=/resource won't match if the actual request was /resource/abcd-ff.
     *
     * <p>Historical note: This was commonly expressed as "http.uri" in zipkin, eventhough it was most
     * often just a path.
     */
    const HTTP_PATH = "http.path";

    /**
     * The entire URL, including the scheme, host and query parameters if available. Ex.
     * "https://mybucket.s3.amazonaws.com/objects/abcd-ff?X-Amz-Algorithm=AWS4-HMAC-SHA256..."
     *
     * <p>Combined with {@linkplain #HTTP_METHOD}, you can understand the fully-qualified request
     * line.
     *
     * <p>This is optional as it may include private data or be of considerable length.
     */
    const HTTP_URL = "http.url";

    /**
     * The HTTP status code, when not in 2xx range. Ex. "503"
     *
     * <p>Used to filter for error status.
     */
    const HTTP_STATUS_CODE = "http.status_code";

    /**
     * The size of the non-empty HTTP request body, in bytes. Ex. "16384"
     *
     * <p>Large uploads can exceed limits or contribute directly to latency.
     */
    const HTTP_REQUEST_SIZE = "http.request.size";

    /**
     * The size of the non-empty HTTP response body, in bytes. Ex. "16384"
     *
     * <p>Large downloads can exceed limits or contribute directly to latency.
     */
    const HTTP_RESPONSE_SIZE = "http.response.size";

    /**
     * The query executed for SQL call.  Ex. "select * from customers where id = ?"
     *
     * <p>Used to understand the complexity of a request
     */
    const SQL_QUERY = "sql.query";

    private function __construct()
    {
    }
}