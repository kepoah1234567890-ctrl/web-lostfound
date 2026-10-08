-- Jalankan pada database lost_found yang sudah ada.
SET @has_no_telepon = (
	SELECT COUNT(*)
	FROM information_schema.columns
	WHERE table_schema = DATABASE()
	  AND table_name = 'users'
	  AND column_name = 'no_telepon'
);

SET @migration_sql = IF(
	@has_no_telepon = 0,
	'ALTER TABLE users ADD COLUMN no_telepon VARCHAR(20) NULL AFTER email',
	'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @has_legacy_telepon = (
	SELECT COUNT(*)
	FROM information_schema.columns
	WHERE table_schema = DATABASE()
	  AND table_name = 'users'
	  AND column_name = 'telepon'
);

SET @migration_sql = IF(
	@has_legacy_telepon > 0,
	CONCAT(
		'UPDATE users SET no_telepon = telepon ',
		'WHERE (no_telepon IS NULL OR no_telepon = ', QUOTE(''), ') ',
		'AND telepon IS NOT NULL AND telepon <> ', QUOTE('')
	),
	'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;
