<?php

namespace Core\Lib;

use PDO;
use PDOStatement;
use Closure;
use InvalidArgumentException;

/**
 * Clase base para Modelos.
 * - Inyección del PDO compartido (Database::pdo()).
 * - Helpers para consultas preparadas (fetchOne, fetchAll, exec).
 * - Helpers de escritura (insert, update, delete) con binding automático.
 * - Transacciones (begin/commit/rollback) y withTransaction().
 * - Paginación consistente (paginate()).
 */
class Model
{
	protected PDO $db;

	public function __construct()
	{
		$this->db = Database::pdo();
	}

	/* =========================================================
     *  TRANSACTIONS
     * ======================================================= */
	public function begin(): void
	{
		if (!$this->db->inTransaction()) {
			$this->db->beginTransaction();
		}
	}

	public function commit(): void
	{
		if ($this->db->inTransaction()) {
			$this->db->commit();
		}
	}

	public function rollback(): void
	{
		if ($this->db->inTransaction()) {
			$this->db->rollBack();
		}
	}

	/**
	 * Ejecuta una callback dentro de una transacción.
	 * Si lanza excepción → rollback y se propaga.
	 */
	public function withTransaction(Closure $fn)
	{
		$this->begin();
		try {
			$result = $fn($this);
			$this->commit();
			return $result;
		} catch (\Throwable $e) {
			$this->rollback();
			throw $e;
		}
	}

	/* =========================================================
     *  SELECT HELPERS
     * ======================================================= */
	/**
	 * Consulta genérica preparada que retorna un statement listo.
	 * Uso interno; preferir fetchOne/fetchAll.
	 */
	protected function prepared(string $sql, array $params = []): PDOStatement
	{
		$stmt = $this->db->prepare($sql);
		foreach ($params as $k => $v) {
			// Permite usar binds posicionales (0,1,2) o nombrados (:foo)
			$param = is_int($k) ? $k + 1 : (str_starts_with((string)$k, ':') ? $k : ':' . $k);
			$stmt->bindValue($param, $v);
		}
		$stmt->execute();
		return $stmt;
	}

	/**
	 * Retorna una sola fila (o null).
	 */
	public function fetchOne(string $sql, array $params = []): ?array
	{
		$stmt = $this->prepared($sql, $params);
		$row  = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row ?: null;
	}

	/**
	 * Retorna todas las filas (array vacío si no hay resultados).
	 */
	protected function fetchAll(string $sql, array $params = []): array
	{
		$stmt = $this->prepared($sql, $params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
	}

	/* =========================================================
     *  WRITE HELPERS
     * ======================================================= */
	/**
	 * Inserta en $table con bind automático.
	 * @return int id autoincrement (si aplica) o 0.
	 */
	protected function insert(string $table, array $data): int
	{
		if (empty($data)) {
			throw new InvalidArgumentException("insert(): data no puede ser vacío");
		}
		$cols = array_keys($data);
		$placeholders = array_map(fn($c) => ':' . $c, $cols);
		$sql = "INSERT INTO {$table} (" . implode(',', $cols) . ")
                VALUES (" . implode(',', $placeholders) . ")";
		$this->prepared($sql, $data);
		return (int)$this->db->lastInsertId();
	}

	/**
	 * Update en $table con bind automático.
	 * @param string $where cláusula sin 'WHERE' (ej: "id = :id")
	 * @param array $params parámetros para el WHERE (además de $data)
	 * @return int filas afectadas
	 */
	protected function update(string $table, array $data, string $where, array $params = []): int
	{
		if (empty($data)) {
			throw new InvalidArgumentException("update(): data no puede ser vacío");
		}
		if (trim($where) === '') {
			throw new InvalidArgumentException("update(): where no puede ser vacío");
		}
		$sets = [];
		foreach (array_keys($data) as $col) {
			$sets[] = "{$col} = :set_{$col}";
		}
		// renombrar binds para no colisionar con params del WHERE
		$binds = [];
		foreach ($data as $col => $val) {
			$binds["set_{$col}"] = $val;
		}
		$binds = array_merge($binds, $params);

		$sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE {$where}";
		$stmt = $this->prepared($sql, $binds);
		return $stmt->rowCount();
	}

	/**
	 * Delete genérico.
	 * @return int filas afectadas
	 */
	protected function delete(string $table, string $where, array $params = []): int
	{
		if (trim($where) === '') {
			throw new InvalidArgumentException("delete(): where no puede ser vacío");
		}
		$sql = "DELETE FROM {$table} WHERE {$where}";
		$stmt = $this->prepared($sql, $params);
		return $stmt->rowCount();
	}

	/* =========================================================
     *  PAGINACIÓN
     * ======================================================= */
	/**
	 * Paginación segura: aplica LIMIT/OFFSET y total exacto opcional.
	 * @param string $baseSql SELECT sin LIMIT/OFFSET
	 * @param array  $params  parámetros del SELECT
	 * @param int    $page    página (>=1)
	 * @param int    $perPage tamaño por página (1..500)
	 * @param bool   $withTotal si true calcula el total (COUNT)
	 * @return array {data, page, per_page, total, total_pages}
	 */
	protected function paginate(string $baseSql, array $params, int $page = 1, int $perPage = 20, bool $withTotal = true): array
	{
		$page = max(1, $page);
		$perPage = max(1, min(500, $perPage));
		$offset = ($page - 1) * $perPage;

		$sql = $baseSql . " LIMIT :_limit OFFSET :_offset";
		$stmt = $this->db->prepare($sql);

		// Bind params originales
		foreach ($params as $k => $v) {
			$param = is_int($k) ? $k + 1 : (str_starts_with((string)$k, ':') ? $k : ':' . $k);
			$stmt->bindValue($param, $v);
		}
		// Bind limit/offset como enteros
		$stmt->bindValue(':_limit', $perPage, PDO::PARAM_INT);
		$stmt->bindValue(':_offset', $offset, PDO::PARAM_INT);
		$stmt->execute();

		$data = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

		$total = null;
		$totalPages = null;
		if ($withTotal) {
			$countSql = "SELECT COUNT(*) FROM (" . $baseSql . ") AS _sub";
			$countStmt = $this->prepared($countSql, $params);
			$total = (int)$countStmt->fetchColumn();
			$totalPages = (int)ceil($total / $perPage);
		}

		return [
			'data' => $data,
			'page' => $page,
			'per_page' => $perPage,
			'total' => $total,
			'total_pages' => $totalPages
		];
	}

	/* =========================================================
     *  HELPERS VARIOS
     * ======================================================= */
	/**
	 * Timestamp actual en UTC (formato MySQL).
	 */
	protected function now(): string
	{
		return gmdate('Y-m-d H:i:s');
	}

	/**
	 * Acceso directo al PDO para casos especializados.
	 */
	public function pdo(): PDO
	{
		return $this->db;
	}
}
