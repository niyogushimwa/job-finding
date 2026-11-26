<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Paginator;
use App\Models\Job;

final class CompanyController extends Controller
{
    public function index(): void
    {
        $q = trim((string) $this->request->query('q', ''));
        $where = ['c.deleted_at IS NULL', "c.status = 'active'"];
        $params = [];
        if ($q !== '') {
            $where[] = '(c.name LIKE :q OR c.description LIKE :q)';
            $params['q'] = "%$q%";
        }
        $sqlWhere = implode(' AND ', $where);
        $total = (int) Database::scalar("SELECT COUNT(*) FROM companies c WHERE $sqlWhere", $params);
        $pager = new Paginator($total, 12, (int) $this->request->query('page', 1));
        $rows = Database::all(
            "SELECT c.*, i.name AS industry_name, (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.status = 'published') AS jobs_count
             FROM companies c LEFT JOIN industries i ON i.id = c.industry_id
             WHERE $sqlWhere ORDER BY c.featured DESC, jobs_count DESC, c.name LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $pager->perPage, 'offset' => $pager->offset])
        );
        $this->render('companies.index', ['companies' => $rows, 'pager' => $pager, 'q' => $q], 'Browse Companies');
    }

    public function show(): void
    {
        $id = (int) $this->request->route('id');
        $company = Database::one(
            'SELECT c.*, i.name AS industry_name, co.name AS country_name FROM companies c
             LEFT JOIN industries i ON i.id = c.industry_id LEFT JOIN countries co ON co.id = c.country_id
             WHERE c.id = :id AND c.deleted_at IS NULL',
            ['id' => $id]
        );
        if (!$company) { abort(404, 'Company not found.'); }
        $pager = new Paginator(0, 6, 1);
        $jobs = Database::all(
            'SELECT ' . Job::SELECT . ' ' . Job::FROM . " WHERE j.company_id = :id AND j.status = 'published' ORDER BY j.published_at DESC LIMIT 12",
            ['id' => $id]
        );
        $following = Auth::id() ? (bool) Database::scalar('SELECT COUNT(*) FROM company_followers WHERE company_id = :c AND user_id = :u', ['c' => $id, 'u' => Auth::id()]) : false;
        $followers = (int) Database::scalar('SELECT COUNT(*) FROM company_followers WHERE company_id = :id', ['id' => $id]);
        $this->render('companies.show', ['company' => $company, 'jobs' => $jobs, 'following' => $following, 'followers' => $followers], $company['name']);
    }

    public function follow(): void
    {
        $companyId = (int) $this->request->route('id');
        $existing = Database::one('SELECT id FROM company_followers WHERE company_id = :c AND user_id = :u', ['c' => $companyId, 'u' => Auth::id()]);
        if ($existing) {
            Database::delete('company_followers', 'id = :id', ['id' => $existing['id']]);
            flash('success', 'You have unfollowed this company.');
        } else {
            Database::insert('company_followers', ['company_id' => $companyId, 'user_id' => Auth::id(), 'created_at' => date('Y-m-d H:i:s')]);
            flash('success', 'You now follow this company. We will notify you about new vacancies.');
        }
        redirect('companies/' . $companyId);
    }
}
