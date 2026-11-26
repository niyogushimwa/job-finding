<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Paginator;

final class Opportunity
{
    public static function fingerprint(array $data): string
    {
        return hash('sha256', mb_strtolower(trim($data['title'] . '|' . $data['organization'] . '|' . ($data['application_url'] ?? ''))));
    }

    public static function duplicate(array $data, ?int $exceptId = null): ?array
    {
        $sql = 'SELECT id, title, organization FROM external_opportunities WHERE fingerprint = :fp';
        $params = ['fp' => self::fingerprint($data)];
        if ($exceptId) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        $row = Database::one($sql, $params);
        if ($row) {
            return $row;
        }
        return Database::one('SELECT id, title, organization FROM external_opportunities WHERE LOWER(title) = :t AND LOWER(organization) = :o', [
            't' => mb_strtolower(trim($data['title'])),
            'o' => mb_strtolower(trim($data['organization'])),
        ]);
    }

    public static function external(array $filters, Paginator $paginator): array
    {
        $where = ["e.status = 'published'"];
        $params = [];
        if (!empty($filters['q'])) {
            $where[] = '(e.title LIKE :q OR e.description LIKE :q OR e.organization LIKE :q)';
            $params['q'] = '%' . trim((string) $filters['q']) . '%';
        }
        if (!empty($filters['category'])) {
            $where[] = 'e.opportunity_category_id = :category';
            $params['category'] = (int) $filters['category'];
        }
        if (!empty($filters['country'])) {
            $where[] = 'e.country_id = :country';
            $params['country'] = (int) $filters['country'];
        }
        if (!empty($filters['qualification'])) {
            $where[] = '(e.requirements LIKE :qual OR e.description LIKE :qual)';
            $params['qual'] = '%' . $filters['qualification'] . '%';
        }
        if (!empty($filters['deadline_before'])) {
            $where[] = 'e.deadline <= :deadline_before';
            $params['deadline_before'] = $filters['deadline_before'];
        }
        if (!empty($filters['deadline_after'])) {
            $where[] = 'e.deadline >= :deadline_after';
            $params['deadline_after'] = $filters['deadline_after'];
        }
        if (!empty($filters['verified'])) {
            $where[] = "e.verification_status = 'verified'";
        }
        if (!empty($filters['featured'])) {
            $where[] = 'e.is_featured = 1';
        }
        $sqlWhere = implode(' AND ', $where);
        $total = (int) Database::scalar("SELECT COUNT(*) FROM external_opportunities e WHERE $sqlWhere", $params);
        $rows = Database::all(
            "SELECT e.*, oc.name AS opportunity_category_name, co.name AS country_name
             FROM external_opportunities e
             LEFT JOIN opportunity_categories oc ON oc.id = e.opportunity_category_id
             LEFT JOIN countries co ON co.id = e.country_id
             WHERE $sqlWhere ORDER BY e.is_featured DESC, e.published_at DESC, e.deadline ASC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $paginator->perPage, 'offset' => $paginator->offset])
        );
        return ['rows' => $rows, 'total' => $total];
    }

    public static function findExternal(int $id): ?array
    {
        return Database::one(
            'SELECT e.*, oc.name AS opportunity_category_name, co.name AS country_name FROM external_opportunities e
             LEFT JOIN opportunity_categories oc ON oc.id = e.opportunity_category_id
             LEFT JOIN countries co ON co.id = e.country_id WHERE e.id = :id',
            ['id' => $id]
        );
    }

    /** Move opportunities whose deadline has passed to expired. */
    public static function expireOld(): int
    {
        return Database::query(
            "UPDATE external_opportunities SET status = 'expired'
             WHERE status = 'published' AND deadline IS NOT NULL AND deadline < CURDATE()"
        )->rowCount();
    }
}
