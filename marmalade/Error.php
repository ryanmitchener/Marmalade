<?php
namespace Marmalade;

use Marmalade\Models\ErrorModel;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** Error constants class */
class Error {
    const NO_ACCEPT_HEADER_VERSION_SET = "no_accept_header_version_set";
    const INVALID_TIMESTAMP_HEADER = "invalid_timestamp_header";
    const INVALID_AUTHORIZATION_HEADER = "invalid_authorization_header";
    const INVALID_REQUEST_CONTENT_TYPE = "invalid_request_content_type";
    const INVALID_JSON_BODY = "invalid_json_body";
    const INVALID_XML_BODY = "invalid_xml_body";
    const INVALID_API_SIGNATURE = "invalid_api_signature";
    const API_REQUEST_EXPIRED = "api_request_expired";
    const API_SIGNATURE_ALREADY_USED = "api_signature_already_used";
    const INVALID_NONCE = "invalid_nonce";
    const NO_TLS = "no_tls";
    const PAGE_NOT_FOUND = "page_not_found";
    const INVALID_RESOURCE_VERSION = "invalid_resource_version";
    const INVALID_XHR_ACTION = "invalid_xhr_action";
    const METHOD_NOT_ALLOWED = "method_not_allowed";
    const NO_DATABASE = "no_database";
    const DATABASE_NOT_CONFIGURED = "database_not_configured";
    const COULD_NOT_WRITE_TO_RESPONSE_STREAM = "could_not_write_to_response_stream";
    const COULD_NOT_READ_FROM_RESPONSE_STREAM = "could_not_read_from_response_stream";


    /** 
     * Create an Error model and return it 
     *
     * @param string $type The type of error to create
     * @param array $params Any additional options to pass in for error creation
     */
    public static function create($type, $params = array()) {
        switch ($type) {
            case self::NO_ACCEPT_HEADER_VERSION_SET:
                return new ErrorModel(400, $type, "The version was not correctly set in the Accept header in the request. The Accept header should look something like: \"Accept: application/".VENDOR_ID.".v1;\"");
            case self::INVALID_TIMESTAMP_HEADER:
                return new ErrorModel(400, $type, "Invalid custom timestamp header. The timestamp is either missing or malformed; it should look something like: \"".APP_NAME."-timestamp: {UTC milliseconds since epoch}\"");
            case self::INVALID_AUTHORIZATION_HEADER:
                return new ErrorModel(400, $type, "Invalid Authorization header. The authorization is either missing or malformed. The Authorization header should look something like: \"Authorization: ".APP_NAME.":{public_key}:{request_signature};\"");
            case self::INVALID_REQUEST_CONTENT_TYPE:
                return new ErrorModel(400, $type, "Invalid Content-Type header. This endpoint requires a content-type of \"{$params["content_type"]}\".");
            case self::INVALID_JSON_BODY:
                return new ErrorModel(400, $type, "Invalid JSON request body.");
            case self::INVALID_XML_BODY:
                return new ErrorModel(400, $type, "Invalid XML request body.");
            case self::INVALID_API_SIGNATURE:
                return new ErrorModel(401, $type, "Invalid API signature.");
            case self::API_REQUEST_EXPIRED:
                return new ErrorModel(401, $type, "API request has expired. The timestamp passed is too old.");
            case self::API_SIGNATURE_ALREADY_USED:
                return new ErrorModel(401, $type, "API request has expired. The passed signature has already been used.");
            case self::INVALID_NONCE:
                return new ErrorModel(403, $type, "Invalid nonce.");
            case self::NO_TLS:
                return new ErrorModel(403, $type, "SSL/TLS required. Please use HTTPS, otherwise sensitive information will pass unencrypted over the Internet.");
            case self::PAGE_NOT_FOUND:
                return new ErrorModel(404, $type, sprintf("%s not found.", (IS_API) ? "Endpoint" : "Page"));
            case self::INVALID_RESOURCE_VERSION:
                return new ErrorModel(404, $type, "Invalid resource version: The requested resource is not available in the requested version");
            case self::INVALID_XHR_ACTION:
                return new ErrorModel(404, $type, "Invalid XHR action. The action \"{$params["action"]}\" does not exist.");
            case self::METHOD_NOT_ALLOWED:
                return new ErrorModel(405, $type, "Method Not Allowed. The HTTP method  \"".Request::$http_verb."\" is not supported for this resource.");
            case self::NO_DATABASE:
                return new ErrorModel(500, $type, "Error connecting to the database.");
            case self::DATABASE_NOT_CONFIGURED:
                return new ErrorModel(500, $type, "Error connecting to the database. The database has not been configured.");
            case self::COULD_NOT_WRITE_TO_RESPONSE_STREAM:
                return new ErrorModel(500, $type, "Could not write data to the response stream.");
            case self::COULD_NOT_READ_FROM_RESPONSE_STREAM:
                return ErrorModel(500, $type, "Could not read data from the response stream.");
        }
    }
}