<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Paginator;
use App\Models\Content;
use App\Models\Opportunity;

final class OpportunityController extends Controller
{
    private const PER_PAGE = 12;

    /** Public radar of curated third-party opportunities. */
    public function radar(): void
    {
        Opportunity::expireOld();
        $filters = $this->filters();
        $page = $this->page();

        $pager = new Paginator(0, self::PER_PAGE, $page);
        $result = Opportunity::external($filters, $pager);
        $pager = new Paginator($result['total'], self::PER_PAGE, $page);
        $result = Opportunity::external($filters, $pager);

        $this->renderRadar([
            'rows' => $result['rows'],
            'pager' => $pager,
            'filters' => $filters,
            'pageHeading' => 'Opportunity Radar',
            'pageIntro' => 'Internships, scholarships, remote roles and projects from trusted external sources — checked, categorised and collected in one place.',
            'activeFilter' => '',
            'hiddenFilters' => [],
            'title' => 'Opportunity Radar',
        ]);
    }

    public function internships(): void
    {
        $this->listing([
            'title' => 'Internships',
            'activeFilter' => 'internships',
            'pageHeading' => 'Internships in Rwanda and beyond',
            'pageIntro' => 'Structured placements, graduate trainee schemes and internships that turn classroom learning into real experience.',
            'categorySlugs' => ['internships', 'graduate-programmes'],
            'keywords' => ['internship', 'graduate trainee'],
            'primarySlug' => 'internships',
        ]);
    }

    public function remote(): void
    {
        $this->listing([
            'title' => 'Remote Opportunities',
            'activeFilter' => 'remote',
            'pageHeading' => 'Remote work, wherever you are',
            'pageIntro' => 'Remote roles and distributed projects you can do from Kigali, Butare or anywhere with a reliable connection.',
            'categorySlugs' => ['remote-opportunities', 'freelance-projects'],
            'keywords' => ['remote', 'work from home', 'virtual'],
            'locationKeyword' => 'remote',
            'primarySlug' => 'remote-opportunities',
        ]);
    }

    public function freelance(): void
    {
        $this->listing([
            'title' => 'Freelance Projects',
            'activeFilter' => 'freelance',
            'pageHeading' => 'Freelance projects worth your skills',
            'pageIntro' => 'Contract work, short projects and paid assignments from organisations hiring outside the traditional job board.',
            'categorySlugs' => ['freelance-projects'],
            'keywords' => ['freelance', 'contract', 'project work'],
            'primarySlug' => 'freelance-projects',
        ]);
    }

    public function scholarships(): void
    {
        $this->listing([
            'title' => 'Scholarships & Fellowships',
            'activeFilter' => 'scholarships',
            'pageHeading' => 'Scholarships and fellowships for ambitious Africans',
            'pageIntro' => 'Funded study, leadership fellowships and training opportunities — always confirm eligibility and deadlines on the official site.',
            'categorySlugs' => ['scholarships', 'fellowships'],
            'keywords' => ['scholarship', 'fellowship', 'fully funded'],
            'primarySlug' => 'scholarships',
        ]);
    }

    /** One public detail page per external opportunity. */
    public function showExternal(): void
    {
        $id = (int) $this->request->route('id');
        $opportunity = Opportunity::findExternal($id);
        if (!$opportunity || $opportunity['status'] !== 'published') {
            abort(404, 'This opportunity is not available.');
        }

        Database::query(
            'UPDATE external_opportunities SET click_count = click_count + 1 WHERE id = :id',
            ['id' => $id]
        );
        $opportunity['click_count'] = (int) ($opportunity['click_count'] ?? 0) + 1;

        $related = [];
        if (!empty($opportunity['opportunity_category_id'])) {
            $related = Database::all(
                "SELECT e.*, oc.name AS opportunity_category_name, co.name AS country_name
                 FROM external_opportunities e
                 LEFT JOIN opportunity_categories oc ON oc.id = e.opportunity_category_id
                 LEFT JOIN countries co ON co.id = e.country_id
                 WHERE e.status = 'published' AND e.id <> :id AND e.opportunity_category_id = :cat
                 ORDER BY e.is_featured DESC, e.published_at DESC, e.deadline ASC LIMIT 3",
                ['id' => $id, 'cat' => (int) $opportunity['opportunity_category_id']]
            );
        }

        $safety = [
            'Never pay a fee to receive an offer, a scholarship or a place on a programme.',
            'Open the official site from the button below and confirm the role, dates and requirements there.',
            'Check whether you have already applied before submitting a second application for the same opportunity.',
            'Protect your personal documents and never share banking details or one-time passwords by email.',
        ];

        $this->render('opportunity.show', [
            'opportunity' => $opportunity,
            'related' => $related,
            'safety' => $safety,
        ], $opportunity['title']);
    }

    /** Shared implementation for the internships, remote, freelance and scholarships pages. */
    private function listing(array $config): void
    {
        Opportunity::expireOld();

        $where = ["e.status = 'published'"];
        $params = [];
        $clauses = [];

        [$slugs, $slugParams] = Database::in($config['categorySlugs']);
        $clauses[] = "oc.slug IN ($slugs)";
        $params = array_merge($params, $slugParams);

        foreach (array_values($config['keywords']) as $i => $keyword) {
            foreach (['e.title', 'e.description', 'e.organization', 'e.requirements'] as $j => $column) {
                $key = 'kw' . $i . '_' . $j;
                $clauses[] = "$column LIKE :$key";
                $params[$key] = '%' . $keyword . '%';
            }
        }

        if (!empty($config['locationKeyword'])) {
            $clauses[] = 'e.location LIKE :loc';
            $params['loc'] = '%' . $config['locationKeyword'] . '%';
        }

        $where[] = '(' . implode(' OR ', $clauses) . ')';
        $sqlWhere = implode(' AND ', $where);
        $page = $this->page();

        $total = (int) Database::scalar(
            "SELECT COUNT(*) FROM external_opportunities e
             LEFT JOIN opportunity_categories oc ON oc.id = e.opportunity_category_id
             WHERE $sqlWhere",
            $params
        );
        $pager = new Paginator($total, self::PER_PAGE, $page);
        $rows = Database::all(
            "SELECT e.*, oc.name AS opportunity_category_name, co.name AS country_name
             FROM external_opportunities e
             LEFT JOIN opportunity_categories oc ON oc.id = e.opportunity_category_id
             LEFT JOIN countries co ON co.id = e.country_id
             WHERE $sqlWhere
             ORDER BY e.is_featured DESC, e.published_at DESC, e.deadline ASC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $pager->perPage, 'offset' => $pager->offset])
        );

        $primaryId = Database::scalar(
            'SELECT id FROM opportunity_categories WHERE slug = :slug',
            ['slug' => $config['primarySlug']]
        );
        $filters = $this->filters();
        if ($primaryId) {
            $filters['category'] = (string) $primaryId;
        }

        $this->renderRadar([
            'rows' => $rows,
            'pager' => $pager,
            'filters' => $filters,
            'pageHeading' => $config['pageHeading'],
            'pageIntro' => $config['pageIntro'],
            'activeFilter' => $config['activeFilter'],
            'hiddenFilters' => ['focus' => $config['activeFilter']],
            'title' => $config['title'],
        ]);
    }

    /** Populate the shared radar view with reference data. */
    private function renderRadar(array $data): void
    {
        $defaults = [
            'categories' => Database::all('SELECT id, name, slug FROM opportunity_categories WHERE active = 1 ORDER BY name'),
            'countries' => Database::all('SELECT id, name FROM countries WHERE active = 1 ORDER BY name'),
            'ads' => Content::ads('radar'),
            'stats' => $this->stats(),
        ];
        $this->render('opportunity.radar', array_merge($defaults, $data), (string) ($data['title'] ?? 'Opportunity Radar'));
    }

    private function stats(): array
    {
        return [
            'published' => (int) Database::scalar("SELECT COUNT(*) FROM external_opportunities WHERE status = 'published'"),
            'verified' => (int) Database::scalar("SELECT COUNT(*) FROM external_opportunities WHERE status = 'published' AND verification_status = 'verified'"),
            'closing_soon' => (int) Database::scalar(
                "SELECT COUNT(*) FROM external_opportunities
                 WHERE status = 'published' AND deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
            ),
            'by_category' => Database::all(
                "SELECT oc.id, oc.name, oc.slug, COUNT(e.id) AS total
                 FROM opportunity_categories oc
                 LEFT JOIN external_opportunities e ON e.opportunity_category_id = oc.id AND e.status = 'published'
                 WHERE oc.active = 1
                 GROUP BY oc.id, oc.name, oc.slug
                 ORDER BY total DESC, oc.name"
            ),
            'by_status' => Database::all(
                "SELECT status, COUNT(*) AS total FROM external_opportunities
                 GROUP BY status ORDER BY FIELD(status, 'published', 'draft', 'expired', 'archived')"
            ),
        ];
    }

    private function filters(): array
    {
        return [
            'q' => trim((string) $this->request->query('q', '')),
            'category' => (string) $this->request->query('category', ''),
            'country' => (string) $this->request->query('country', ''),
            'qualification' => trim((string) $this->request->query('qualification', '')),
            'deadline_before' => (string) $this->request->query('deadline_before', ''),
            'deadline_after' => (string) $this->request->query('deadline_after', ''),
            'verified' => (string) $this->request->query('verified', ''),
            'featured' => (string) $this->request->query('featured', ''),
        ];
    }

    private function page(): int
    {
        return max(1, (int) $this->request->query('page', 1));
    }
}
