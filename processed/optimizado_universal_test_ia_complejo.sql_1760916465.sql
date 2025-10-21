-- Tabla sensores_iot optimizada por IA UNIVERSAL
INSERT INTO sensores_iot (col_1, col_2, col_3, col_4, col_5, col_6, col_7, col_8, col_9, col_10) VALUES
('550e8400-e29b-41d4-a716-446655440000', '1698765432', 23.45, 67.8, '1013.25 hPa', '40.7128,-74.0060', '3.7', 'online', '{"firmware":"v2.1","calibrated":true}', 'A1B2C3D4'),
('550e8400-e29b-41d4-a716-446655440001', NULL, 15.2, 101.5, 'N/A', 'INVALID_GPS', '0.0', 'error', NULL, NULL),
(NULL, '1698765433', 999.99, 10.0, '0', '0,0', NULL, 'offline', '{"error":"sensor_fault"}', 'INVALID'),
('550e8400-e29b-41d4-a716-446655440002', '1698765434', NULL, NULL, '1000', '40.7128, -74.0060', '3.2', 'mantenimiento', '{"last_maintenance":"2023-10-30"}', 'B2C3D4E5'),
('550e8400-e29b-41d4-a716-446655440003', '1698765435', 18.7, 45.3, '1015.8 hPa', NULL, '2.9', 'online', '{"firmware":"v1.9","needs_update":true}', 'C3D4E5F6'),
('MALFORMED-UUID-123', 'INVALID_TIME', 0.0, 0.0, NULL, NULL, 'LOW', 'unknown', '{}', NULL);

-- Tabla blockchain_transactions optimizada por IA UNIVERSAL
INSERT INTO blockchain_transactions (col_1, col_2, col_3, col_4, col_5, col_6, col_7, col_8, col_9, col_10, col_11, col_12) VALUES
('0x1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef', '18500000', '0x742d35Cc6634C0532925a3b8D4C0532925a3b8D4', '0x8ba1f109551bD432803012645Hac136c0532925a', '1000000000000000000', 21000.0, '20.5', '42', '0x', 1.0, '2023-10-30 14:30:25', 'mainnet'),
('INVALID_HASH', NULL, NULL, '0x0000000000000000000000000000000000000000', 'ZERO', 1.0, '0.0', NULL, NULL, 0.0, NULL, NULL),
('0xabcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890', '18500001', '0x742d35Cc6634C0532925a3b8D4C0532925a3b8D4', NULL, '50000000000000000000', 50000.0, 'HIGH', '43', '0xa9059cbb000000000000000000000000742d35cc6634c0532925a3b8d4c0532925a3b8d4', 1.0, '2023-10-30 14:31:00', 'testnet'),
(NULL, '0', 'GENESIS', 'GENESIS', '0', 0.0, '0', '0', 'GENESIS_BLOCK', 1.0, '1970-01-01 00:00:00', 'genesis'),
('0x9999999999999999999999999999999999999999999999999999999999999999', '-1', '0xDEADBEEF', '0xCAFEBABE', 'INVALID_VALUE', 999999999.0, '-50.0', 'NONCE_ERROR', 'MALFORMED_DATA', 2.0, '2099-12-31 23:59:59', 'unknown');

-- Tabla system_logs optimizada por IA UNIVERSAL
INSERT INTO system_logs (severity_level, component_name, exception_stacktrace, user_session_id, hostname, process_id, memory_usage_mb, cpu_usage_percent, disk_io_bytes, network_latency_ms, custom_fields, created_at, timezone_offset) VALUES
('ERROR', 'AuthenticationService', 'java.lang.SecurityException: Invalid credentials\n\tat com.example.auth.AuthService.authenticate(AuthService.java:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('LoginController.java:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('FATAL', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('DEBUG', 'DatabaseConnectionPool', NULL, 'Sess_Abcdef1234567890', 'Db-Server-02.Example.Com', 54321.0, 1024.0, 12.7, '2097152', '5', '{"pool_size":10,"active_connections":3}', '2023-10-30 14:30:26.789012', '-05:00'),
('WARN', 'CacheManager', NULL, 'Sess_Fedcba0987654321', 'Cache-Server-01.Example.Com', 98765.0, 256.25, 67.8, '512000', '25', '{"cache_size":"500MB","eviction_policy":"LRU"}', '2023-10-30 14:30:27.345678', '+02:00'),
('INFO', 'PaymentProcessor', NULL, 'Sess_1111222233334444', 'Payment-Server-01.Example.Com', 11111.0, 128.5, 23.4, '1024000', '75', '{"amount":"$99.99","currency":"USD","gateway":"stripe"}', '2023-10-30 14:30:28.901234', '-08:00'),
('TRACE', 'Invalid_Component', 'STACK_TRACE_ERROR', 'Invalid_Session', 'Invalid_Host', 1.0, 999.99, 150.0, '-1', '99999', '{"malformed":true,"test":null}', '1900-01-01 00:00:00.000000', 'INVALID');

-- Tabla performance_metrics optimizada por IA UNIVERSAL
INSERT INTO performance_metrics (col_1, col_2, col_3, col_4, col_5, col_6, col_7, col_8, col_9, col_10, col_11, col_12, col_13, col_14, col_15, col_16, col_17, col_18) VALUES
('f47ac10b-58cc-4372-a567-0e02b2c3d479', '1698765432000', 'cpu_utilization_percent', '78.5432109876', 'percentage', '5.234', '73.3091', '83.7773', '1000', '0.001', '0.85', '1.234567', '0.7234', 'TRUE', '0.95', 'automated', 'validated', 'production,critical'),
(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('g58bd21c-69dd-5483-b678-1f13c3d4e590', '1698765433000', 'memory_usage_bytes', '8589934592.0', 'bytes', '1048576.789', '8388607.212', '8791261.788', '500', '0.05', '-0.23', '-0.000123', '0.0567', 'FALSE', '0.87', 'manual', 'pending', 'staging'),
('INVALID-UUID-FORMAT', 'INVALID_TIMESTAMP', 'network_latency_microseconds', 'NOT_A_NUMBER', 'microseconds', 'INVALID', 'INVALID', 'INVALID', '-1', 'INVALID', '999.99', 'INVALID', 'INVALID', 'INVALID', 'INVALID', 'estimated', 'failed', 'development,deprecated'),
('h69ce32d-7aee-6594-c789-2g24d4e5f6a1', '1698765434000', 'disk_io_operations_per_second', '15678.9012345678', 'ops/sec', '234.567', '15444.334', '15913.468', '2000', '0.0001', '0.92', '2.345678', '0.8456', 'TRUE', '0.99', 'calculated', 'validated', 'production');

-- Tabla application_config optimizada por IA UNIVERSAL
INSERT INTO application_config (col_1, col_2, col_3, col_4, col_5, col_6, col_7, col_8, col_9, col_10, col_11, col_12, col_13, col_14, col_15) VALUES
('database.connection.primary', 'production', 'user-service', '2.1', '{"host":"db-prod-01.example.com","port":5432,"database":"users","ssl":true,"pool_size":20,"timeout":30000}', '0x89504E470D0A1A0A', 'a1b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef123456', 'admin@example.com', '2023-10-30 14:30:25.123456', '1', '2.0', 'production', '{"feature_x":true,"feature_y":false,"beta_features":["new_ui","advanced_search"]}', 'redis-service,auth-service', '{"type":"object","properties":{"host":{"type":"string"},"port":{"type":"integer"}}}'),
(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('cache.redis.cluster', 'staging', 'cache-service', '1.5', 'INVALID_JSON{host:redis-staging,port:6379}', '0xDEADBEEF', 'INVALID_CHECKSUM', 'developer@example.com', '2023-10-30 14:30:26.789012', '0', NULL, 'staging', '{"cache_enabled":true,"ttl":3600}', 'monitoring-service', 'INVALID_SCHEMA'),
('api.rate.limiting', 'development', 'api-gateway', '3.0', '{"requests_per_minute":1000,"burst_limit":1500,"whitelist":["192.168.1.0/24"],"blacklist":[]}', NULL, 'b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef12345678', 'devops@example.com', '2023-10-30 14:30:27.345678', '1', '2.9', 'development', '{"rate_limiting":true,"dynamic_limits":false}', NULL, '{"type":"object","required":["requests_per_minute"]}'),
('MALFORMED_CONFIG', 'UNKNOWN', 'UNKNOWN_SERVICE', '-1.0', 'CORRUPTED_DATA', '0x00000000', NULL, 'unknown_user@gmail.com', '1970-01-01 00:00:00.000000', '-1', '-1.0', 'rollback', 'INVALID_JSON', 'MISSING_DEPENDENCIES', 'CORRUPTED_SCHEMA');

-- Tabla chaos_data optimizada por IA UNIVERSAL
INSERT INTO chaos_data (col_1, col_2, col_3, col_4, col_5, col_6, col_7, col_8, col_9, col_10, col_11, col_12, col_13, col_14, col_15) VALUES
('NORMAL_ID_001', '2023-10-30 14:30:25', '123.456', 'Normal text here', '0x48656C6C6F', '{"normal": "json"}', 'normal', 'a,b,c', '123.456789012345678901234567890', '123.456', '2023-10-30 14:30:25.123456', '2023', '14:30:25.123456', 'b1010101010101010', 'ST_GeomFromText(POINT(1 1'),
('POINT(2 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('POLYGON((0 0,0 1,1 1,1 0,0 0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('🚀💀∞αβγδεζηθικλμνξοπρστυφχψω', '1970-01-01T00:00:00Z|2038-01-19T03:14:07Z|9999-12-31T23:59:59Z', '-∞|+∞|NaN|1.7976931348623157E+308|-1.7976931348623157E+308', 'Ñoño José María Ángel Ümlaüt Çedilla 中文 日本語 العربية Русский ελληνικά עברית', '0xDEADBEEFCAFEBABE', '{"nested":{"deeply":{"very":{"much":{"so":{"wow":{"such":{"json":{"many":{"levels":null}}}}}}}}},"array":[1,2,3,null,"string",true,false,{"object":"inside"}],"unicode":"🎉💀∞","numbers":[1.7976931348623157e+308,-1.7976931348623157e+308,null]}', '🚀', 'α,β,γ,δ,ε,ζ,η,θ,ι,κ,λ,μ,ν,ξ,ο,π,ρ,σ,τ,υ,φ,χ,ψ,ω', '99999999999999999999999999999999999.999999999999999999999999999999', '1.7976931348623157E+308', '9999-12-31 23:59:59.999999', '9999', '23:59:59.999999', 'b1111111111111111111111111111111111111111111111111111111111111111', 'ST_GeomFromText(MULTIPOLYGON(((0 0,0 1,1 1,1 0,0 0'),
('(2 2', '2 3', '3 3', '3 2', '2 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('POINT(-180 -90', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('POLYGON((-180 -90,-180 90,180 90,180 -90,-180 -90', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('MALFORMED\\x00\\xFF\\xDEAD', 'INVALID_DATE_FORMAT', 'NOT_A_NUMBER_AT_ALL', 'CORRUPTED_ENCODING_\x80\x81\x82\x83', '0x00000000FFFFFFFF', '{"malformed":json,"missing_quotes":value,"trailing_comma":true,}', 'DROP TABLE', 'INVALID_SET_VALUE', '-99999999999999999999999999999999999.999999999999999999999999999999', '-1.7976931348623157E+308', '0000-00-00 00:00:00.000000', '0000', '-838:59:59.000000', 'b0000000000000000000000000000000000000000000000000000000000000000', NULL);
