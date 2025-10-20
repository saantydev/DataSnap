-- Base de datos compleja para testing de IA Universal
-- Contiene datos inconsistentes, formatos raros y estructuras complejas

-- Tabla de sensores IoT con datos temporales
CREATE TABLE sensores_iot (
    sensor_uuid VARCHAR(36),
    timestamp_unix BIGINT,
    temperatura_celsius DECIMAL(5,2),
    humedad_porcentaje FLOAT,
    presion_atmosferica VARCHAR(20),
    coordenadas_gps TEXT,
    bateria_voltaje DOUBLE,
    estado_conexion ENUM('online','offline','mantenimiento','error'),
    metadata_json JSON,
    checksum_crc32 VARCHAR(8)
);

INSERT INTO sensores_iot VALUES
('550e8400-e29b-41d4-a716-446655440000', 1698765432, 23.45, 67.8, '1013.25 hPa', '40.7128,-74.0060', 3.7, 'online', '{"firmware":"v2.1","calibrated":true}', 'A1B2C3D4'),
('550e8400-e29b-41d4-a716-446655440001', NULL, -15.2, 101.5, 'N/A', 'INVALID_GPS', 0.0, 'error', NULL, ''),
('', 1698765433, 999.99, -10.0, '0', '0,0', NULL, 'offline', '{"error":"sensor_fault"}', 'INVALID'),
('550e8400-e29b-41d4-a716-446655440002', 1698765434, 'HOT', '50%', '1000', '40.7128, -74.0060', 3.2, 'mantenimiento', '{"last_maintenance":"2023-10-30"}', 'B2C3D4E5'),
('550e8400-e29b-41d4-a716-446655440003', 1698765435, 18.7, 45.3, '1015.8 hPa', NULL, 2.9, 'online', '{"firmware":"v1.9","needs_update":true}', 'C3D4E5F6'),
('MALFORMED-UUID-123', 'INVALID_TIME', 0.0, 0.0, '', '', 'LOW', 'unknown', '{}', NULL);

-- Tabla de transacciones blockchain con hashes complejos
CREATE TABLE blockchain_transactions (
    tx_hash VARCHAR(64),
    block_height BIGINT UNSIGNED,
    from_address VARCHAR(42),
    to_address VARCHAR(42),
    value_wei VARCHAR(78),
    gas_used INT,
    gas_price_gwei DECIMAL(20,9),
    nonce BIGINT,
    input_data LONGTEXT,
    transaction_status TINYINT,
    confirmation_time TIMESTAMP,
    network_id VARCHAR(10)
);

INSERT INTO blockchain_transactions VALUES
('0x1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef', 18500000, '0x742d35Cc6634C0532925a3b8D4C0532925a3b8D4', '0x8ba1f109551bD432803012645Hac136c0532925a', '1000000000000000000', 21000, 20.5, 42, '0x', 1, '2023-10-30 14:30:25', 'mainnet'),
('INVALID_HASH', NULL, '', '0x0000000000000000000000000000000000000000', 'ZERO', -1, 0.0, NULL, '', 0, NULL, ''),
('0xabcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890', 18500001, '0x742d35Cc6634C0532925a3b8D4C0532925a3b8D4', NULL, '50000000000000000000', 50000, 'HIGH', 43, '0xa9059cbb000000000000000000000000742d35cc6634c0532925a3b8d4c0532925a3b8d4', 1, '2023-10-30 14:31:00', 'testnet'),
('', 0, 'GENESIS', 'GENESIS', '0', 0, 0, 0, 'GENESIS_BLOCK', 1, '1970-01-01 00:00:00', 'genesis'),
('0x9999999999999999999999999999999999999999999999999999999999999999', -1, '0xDEADBEEF', '0xCAFEBABE', 'INVALID_VALUE', 999999999, -50.0, 'NONCE_ERROR', 'MALFORMED_DATA', 2, '2099-12-31 23:59:59', 'unknown');

-- Tabla de logs de sistema con timestamps complejos
CREATE TABLE system_logs (
    log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    severity_level ENUM('TRACE','DEBUG','INFO','WARN','ERROR','FATAL'),
    component_name VARCHAR(100),
    message MEDIUMTEXT,
    exception_stacktrace LONGTEXT,
    user_session_id VARCHAR(128),
    request_id VARCHAR(36),
    correlation_id VARCHAR(64),
    thread_id VARCHAR(20),
    hostname VARCHAR(255),
    process_id INT,
    memory_usage_mb DECIMAL(10,2),
    cpu_usage_percent FLOAT,
    disk_io_bytes BIGINT,
    network_latency_ms SMALLINT,
    custom_fields JSON,
    created_at DATETIME(6),
    timezone_offset VARCHAR(6)
);

INSERT INTO system_logs (severity_level, component_name, message, exception_stacktrace, user_session_id, request_id, correlation_id, thread_id, hostname, process_id, memory_usage_mb, cpu_usage_percent, disk_io_bytes, network_latency_ms, custom_fields, created_at, timezone_offset) VALUES
('ERROR', 'AuthenticationService', 'Failed login attempt for user: admin@example.com', 'java.lang.SecurityException: Invalid credentials\n\tat com.example.auth.AuthService.authenticate(AuthService.java:45)\n\tat com.example.web.LoginController.doPost(LoginController.java:23)', 'sess_1234567890abcdef', '550e8400-e29b-41d4-a716-446655440000', 'corr_abc123def456', 'pool-1-thread-5', 'web-server-01.example.com', 12345, 512.75, 85.3, 1048576, 150, '{"ip_address":"192.168.1.100","user_agent":"Mozilla/5.0"}', '2023-10-30 14:30:25.123456', '+00:00'),
('FATAL', '', 'SYSTEM CRASH - OUT OF MEMORY', NULL, '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, ''),
('DEBUG', 'DatabaseConnectionPool', 'Connection acquired from pool', '', 'sess_abcdef1234567890', NULL, 'corr_def456ghi789', 'pool-2-thread-1', 'db-server-02.example.com', 54321, 1024.0, 12.7, 2097152, 5, '{"pool_size":10,"active_connections":3}', '2023-10-30 14:30:26.789012', '-05:00'),
('WARN', 'CacheManager', 'Cache hit ratio below threshold: 45%', '', 'sess_fedcba0987654321', '550e8400-e29b-41d4-a716-446655440001', '', 'cache-thread-3', 'cache-server-01.example.com', 98765, 256.25, 67.8, 512000, 25, '{"cache_size":"500MB","eviction_policy":"LRU"}', '2023-10-30 14:30:27.345678', '+02:00'),
('INFO', 'PaymentProcessor', 'Payment processed successfully', '', 'sess_1111222233334444', '550e8400-e29b-41d4-a716-446655440002', 'corr_payment_12345', 'payment-thread-7', 'payment-server-01.example.com', 11111, 128.5, 23.4, 1024000, 75, '{"amount":"$99.99","currency":"USD","gateway":"stripe"}', '2023-10-30 14:30:28.901234', '-08:00'),
('TRACE', 'INVALID_COMPONENT', 'MALFORMED LOG ENTRY', 'STACK_TRACE_ERROR', 'INVALID_SESSION', 'INVALID_REQUEST', 'INVALID_CORRELATION', 'INVALID_THREAD', 'INVALID_HOST', -1, -999.99, 150.0, -1, 99999, '{"malformed":true,"test":null}', '1900-01-01 00:00:00.000000', 'INVALID');

-- Tabla de métricas de rendimiento con datos científicos
CREATE TABLE performance_metrics (
    metric_uuid CHAR(36),
    measurement_timestamp BIGINT,
    metric_name VARCHAR(200),
    metric_value DECIMAL(20,10),
    unit_of_measurement VARCHAR(50),
    standard_deviation DOUBLE,
    confidence_interval_lower DECIMAL(15,8),
    confidence_interval_upper DECIMAL(15,8),
    sample_size INT,
    p_value SCIENTIFIC_NOTATION,
    correlation_coefficient FLOAT,
    regression_slope DECIMAL(12,6),
    r_squared DOUBLE,
    outliers_detected BOOLEAN,
    data_quality_score DECIMAL(3,2),
    collection_method ENUM('automated','manual','calculated','estimated'),
    validation_status VARCHAR(20),
    metadata_tags SET('production','staging','development','critical','deprecated')
);

INSERT INTO performance_metrics VALUES
('f47ac10b-58cc-4372-a567-0e02b2c3d479', 1698765432000, 'cpu_utilization_percent', 78.5432109876, 'percentage', 5.234, 73.3091, 83.7773, 1000, '0.001', 0.85, 1.234567, 0.7234, TRUE, 0.95, 'automated', 'validated', 'production,critical'),
('', NULL, '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', ''),
('g58bd21c-69dd-5483-b678-1f13c3d4e590', 1698765433000, 'memory_usage_bytes', 8589934592.0, 'bytes', 1048576.789, 8388607.212, 8791261.788, 500, '0.05', -0.23, -0.000123, 0.0567, FALSE, 0.87, 'manual', 'pending', 'staging'),
('INVALID-UUID-FORMAT', 'INVALID_TIMESTAMP', 'network_latency_microseconds', 'NOT_A_NUMBER', 'microseconds', 'INVALID', 'INVALID', 'INVALID', -1, 'INVALID', 999.99, 'INVALID', 'INVALID', 'INVALID', 'INVALID', 'estimated', 'failed', 'development,deprecated'),
('h69ce32d-7aee-6594-c789-2g24d4e5f6a1', 1698765434000, 'disk_io_operations_per_second', 15678.9012345678, 'ops/sec', 234.567, 15444.334, 15913.468, 2000, '0.0001', 0.92, 2.345678, 0.8456, TRUE, 0.99, 'calculated', 'validated', 'production');

-- Tabla de configuraciones con estructuras anidadas
CREATE TABLE application_config (
    config_id VARCHAR(100),
    environment VARCHAR(20),
    service_name VARCHAR(100),
    config_version DECIMAL(5,2),
    config_data LONGTEXT,
    encrypted_secrets BLOB,
    checksum_sha256 VARCHAR(64),
    last_modified_by VARCHAR(100),
    last_modified_timestamp TIMESTAMP(6),
    is_active TINYINT(1),
    rollback_version DECIMAL(5,2),
    deployment_stage ENUM('development','testing','staging','production','rollback'),
    feature_flags JSON,
    dependencies TEXT,
    validation_schema MEDIUMTEXT
);

INSERT INTO application_config VALUES
('database.connection.primary', 'production', 'user-service', 2.1, '{"host":"db-prod-01.example.com","port":5432,"database":"users","ssl":true,"pool_size":20,"timeout":30000}', 0x89504E470D0A1A0A, 'a1b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef123456', 'admin@example.com', '2023-10-30 14:30:25.123456', 1, 2.0, 'production', '{"feature_x":true,"feature_y":false,"beta_features":["new_ui","advanced_search"]}', 'redis-service,auth-service', '{"type":"object","properties":{"host":{"type":"string"},"port":{"type":"integer"}}}'),
('', '', '', NULL, '', NULL, '', '', NULL, NULL, NULL, '', NULL, '', ''),
('cache.redis.cluster', 'staging', 'cache-service', 1.5, 'INVALID_JSON{host:redis-staging,port:6379}', 0xDEADBEEF, 'INVALID_CHECKSUM', 'developer@example.com', '2023-10-30 14:30:26.789012', 0, NULL, 'staging', '{"cache_enabled":true,"ttl":3600}', 'monitoring-service', 'INVALID_SCHEMA'),
('api.rate.limiting', 'development', 'api-gateway', 3.0, '{"requests_per_minute":1000,"burst_limit":1500,"whitelist":["192.168.1.0/24"],"blacklist":[]}', NULL, 'b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef12345678', 'devops@example.com', '2023-10-30 14:30:27.345678', 1, 2.9, 'development', '{"rate_limiting":true,"dynamic_limits":false}', '', '{"type":"object","required":["requests_per_minute"]}'),
('MALFORMED_CONFIG', 'UNKNOWN', 'UNKNOWN_SERVICE', -1.0, 'CORRUPTED_DATA', 0x00000000, '', 'UNKNOWN_USER', '1970-01-01 00:00:00.000000', -1, -1.0, 'rollback', 'INVALID_JSON', 'MISSING_DEPENDENCIES', 'CORRUPTED_SCHEMA');

-- Tabla de eventos de auditoría con datos forenses
CREATE TABLE audit_events (
    event_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_uuid BINARY(16),
    event_type VARCHAR(50),
    actor_id VARCHAR(100),
    actor_type ENUM('user','system','service','anonymous','bot'),
    target_resource VARCHAR(200),
    action_performed VARCHAR(100),
    event_timestamp TIMESTAMP(6),
    source_ip_address VARBINARY(16),
    user_agent TEXT,
    session_id VARCHAR(128),
    request_headers JSON,
    request_payload LONGTEXT,
    response_status_code SMALLINT,
    response_payload LONGTEXT,
    execution_time_ms DECIMAL(10,3),
    risk_score DECIMAL(4,2),
    anomaly_detected BOOLEAN,
    geolocation_data JSON,
    device_fingerprint VARCHAR(256),
    compliance_tags SET('gdpr','hipaa','sox','pci','iso27001'),
    retention_policy VARCHAR(50),
    encrypted_pii VARBINARY(1000)
);

INSERT INTO audit_events (event_uuid, event_type, actor_id, actor_type, target_resource, action_performed, event_timestamp, source_ip_address, user_agent, session_id, request_headers, request_payload, response_status_code, response_payload, execution_time_ms, risk_score, anomaly_detected, geolocation_data, device_fingerprint, compliance_tags, retention_policy, encrypted_pii) VALUES
(UNHEX('550e8400e29b41d4a716446655440000'), 'authentication', 'user123@example.com', 'user', '/api/v1/auth/login', 'LOGIN_ATTEMPT', '2023-10-30 14:30:25.123456', INET6_ATON('192.168.1.100'), 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'sess_abc123def456', '{"Content-Type":"application/json","Authorization":"Bearer token123"}', '{"username":"user123@example.com","password":"[REDACTED]"}', 200, '{"success":true,"token":"jwt_token_here"}', 245.678, 2.5, FALSE, '{"country":"US","city":"New York","lat":40.7128,"lon":-74.0060}', 'fp_1234567890abcdef', 'gdpr,hipaa', '7_years', 0x89504E470D0A1A0A),
(NULL, '', '', 'anonymous', '', '', NULL, NULL, '', '', NULL, '', NULL, '', NULL, NULL, NULL, NULL, '', '', '', NULL),
(UNHEX('660f9511f3ac52e5b827557766551111'), 'data_access', 'service_account_analytics', 'service', '/api/v1/users/sensitive-data', 'READ_PII', '2023-10-30 14:30:26.789012', INET6_ATON('10.0.0.50'), 'ServiceBot/1.0', 'service_sess_789', '{"X-Service-Token":"service123","X-Request-ID":"req_456"}', '{"user_ids":[1,2,3,4,5]}', 200, '{"users":[{"id":1,"email":"[REDACTED]"}]}', 1234.567, 8.9, TRUE, '{"country":"US","datacenter":"us-east-1"}', 'service_fp_abcdef123456', 'gdpr,hipaa,sox', '10_years', 0xDEADBEEFCAFEBABE),
(UNHEX('INVALID_BINARY_DATA'), 'MALFORMED_EVENT', 'UNKNOWN_ACTOR', 'bot', 'INVALID_RESOURCE', 'SUSPICIOUS_ACTION', '1900-01-01 00:00:00.000000', 0x00000000, 'MALICIOUS_BOT/666', 'INVALID_SESSION', '{"MALFORMED":"JSON}', 'CORRUPTED_PAYLOAD', -1, 'ERROR_RESPONSE', -999.999, 10.0, TRUE, 'INVALID_GEO', 'SUSPICIOUS_FINGERPRINT', 'pci,iso27001', 'INDEFINITE', 0x0000000000000000);

-- Tabla final con datos extremadamente complejos y corruptos
CREATE TABLE chaos_data (
    id VARCHAR(255),
    timestamp_variants TEXT,
    numeric_chaos LONGTEXT,
    text_encoding_mess MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    binary_garbage LONGBLOB,
    json_nightmare JSON,
    enum_madness ENUM('','normal','ñoño','🚀','NULL','null','undefined','NaN','Infinity','-Infinity','true','false','0','1','[]','{}','<script>','DROP TABLE'),
    set_confusion SET('a','b','c','','null','NULL','🎉','💀','∞','α','β','γ','δ','ε','ζ','η','θ','ι','κ','λ','μ','ν','ξ','ο','π','ρ','σ','τ','υ','φ','χ','ψ','ω'),
    decimal_precision DECIMAL(65,30),
    float_extremes DOUBLE,
    date_time_chaos DATETIME(6),
    year_field YEAR,
    time_field TIME(6),
    bit_field BIT(64),
    geometry_data GEOMETRY,
    point_data POINT,
    polygon_data POLYGON
);

INSERT INTO chaos_data VALUES
('NORMAL_ID_001', '2023-10-30 14:30:25', '123.456', 'Normal text here', 0x48656C6C6F, '{"normal": "json"}', 'normal', 'a,b,c', 123.456789012345678901234567890, 123.456, '2023-10-30 14:30:25.123456', 2023, '14:30:25.123456', b'1010101010101010', ST_GeomFromText('POINT(1 1)'), ST_PointFromText('POINT(2 2)'), ST_PolygonFromText('POLYGON((0 0,0 1,1 1,1 0,0 0))')),
('', '', '', '', NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('🚀💀∞αβγδεζηθικλμνξοπρστυφχψω', '1970-01-01T00:00:00Z|2038-01-19T03:14:07Z|9999-12-31T23:59:59Z', '-∞|+∞|NaN|1.7976931348623157E+308|-1.7976931348623157E+308', 'Ñoño José María Ángel Ümlaüt Çedilla 中文 日本語 العربية Русский ελληνικά עברית', 0xDEADBEEFCAFEBABE, '{"nested":{"deeply":{"very":{"much":{"so":{"wow":{"such":{"json":{"many":{"levels":null}}}}}}}}},"array":[1,2,3,null,"string",true,false,{"object":"inside"}],"unicode":"🎉💀∞","numbers":[1.7976931348623157e+308,-1.7976931348623157e+308,null]}', '🚀', 'α,β,γ,δ,ε,ζ,η,θ,ι,κ,λ,μ,ν,ξ,ο,π,ρ,σ,τ,υ,φ,χ,ψ,ω', 99999999999999999999999999999999999.999999999999999999999999999999, 1.7976931348623157E+308, '9999-12-31 23:59:59.999999', 9999, '23:59:59.999999', b'1111111111111111111111111111111111111111111111111111111111111111', ST_GeomFromText('MULTIPOLYGON(((0 0,0 1,1 1,1 0,0 0)),((2 2,2 3,3 3,3 2,2 2)))'), ST_PointFromText('POINT(-180 -90)'), ST_PolygonFromText('POLYGON((-180 -90,-180 90,180 90,180 -90,-180 -90))')),
('MALFORMED\\x00\\xFF\\xDEAD', 'INVALID_DATE_FORMAT', 'NOT_A_NUMBER_AT_ALL', 'CORRUPTED_ENCODING_\x80\x81\x82\x83', 0x00000000FFFFFFFF, '{"malformed":json,"missing_quotes":value,"trailing_comma":true,}', 'DROP TABLE', 'INVALID_SET_VALUE', -99999999999999999999999999999999999.999999999999999999999999999999, -1.7976931348623157E+308, '0000-00-00 00:00:00.000000', 0000, '-838:59:59.000000', b'0000000000000000000000000000000000000000000000000000000000000000', NULL, NULL, NULL);