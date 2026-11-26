<?php
declare(strict_types=1);

namespace App\Core;

/** Lightweight translation layer. Interface strings are translatable; authored content is not. */
final class I18n
{
    private static string $locale = 'en';
    private static array $supported = [];

    private const STRINGS = [
        'en' => [
            'nav_home' => 'Home', 'nav_jobs' => 'Find Jobs', 'nav_companies' => 'Companies',
            'nav_opportunities' => 'Opportunities', 'nav_career' => 'Career Centre', 'nav_community' => 'Community',
            'nav_login' => 'Log in', 'nav_register' => 'Register', 'nav_post_job' => 'Post a Job',
            'hero_title' => 'Your Next Opportunity Starts Here.', 'hero_text' => 'Discover jobs, internships, remote opportunities and career resources. Connect with employers and take the next step towards your future.',
            'search_placeholder' => 'Job title, skill or keyword', 'location_placeholder' => 'City, province or remote', 'search' => 'Search opportunities',
            'view_jobs' => 'View opportunities', 'learn_more' => 'Learn more', 'apply_now' => 'Apply now', 'save' => 'Save',
            'empty_title' => 'Nothing here yet', 'empty_text' => 'Check back soon — new opportunities are added every day.',
            'no_results' => 'No opportunities matched your search.', 'clear_filters' => 'Clear filters',
            'results' => 'opportunities', 'view_all' => 'View all', 'dashboard' => 'Dashboard',
            'logout' => 'Log out', 'profile' => 'My profile', 'saved_jobs' => 'Saved jobs', 'applications' => 'My applications',
            'employer_area' => 'Employer area', 'admin_area' => 'Administration', 'language' => 'Language',
            'featured' => 'Featured', 'remote' => 'Remote', 'new' => 'New', 'deadline' => 'Deadline', 'location' => 'Location',
            'salary' => 'Salary', 'category' => 'Category', 'published' => 'Published', 'verified' => 'Verified',
            'external' => 'External opportunity', 'details' => 'View details', 'contact' => 'Contact us',
        ],
        'rw' => [
            'nav_home' => 'Ahabanza', 'nav_jobs' => 'Shakisha Akazi', 'nav_companies' => 'Ibigo',
            'nav_opportunities' => 'Amahirwe', 'nav_career' => 'Ibizigama Umwuga', 'nav_community' => 'Umuryango',
            'nav_login' => 'Kwinjira', 'nav_register' => 'Kwiyandikisha', 'nav_post_job' => 'Shyira Akazi',
            'hero_title' => 'Amahirwe yawe ya mbere atangira hano.', 'hero_text' => 'Shakisha akazi, ubufasha, akazi ka kure n’ibikoresho by’umwuga. Huza n’abakoresha wigire imbere.',
            'search_placeholder' => 'Izina ry’akazi, ubumenyi cyangwa ijambo', 'location_placeholder' => 'Umujyi, intara cyangwa kure', 'search' => 'Shakisha',
            'view_jobs' => 'Reba amahirwe', 'learn_more' => 'Menya byinshi', 'apply_now' => 'Saba akazi', 'save' => 'Bika',
            'empty_title' => 'Nta kintu kiri hano', 'empty_text' => 'Subira urebe vuba — amahirwe mashya yongerwa buri munsi.',
            'no_results' => 'Nta mahirwe yaboneka.', 'clear_filters' => 'Sura muyunguruzi',
            'results' => 'amahirwe', 'view_all' => 'Reba byose', 'dashboard' => 'Ikibaho',
            'logout' => 'Sohoka', 'profile' => 'Umwirondoro wanjye', 'saved_jobs' => 'Akazi kabitswe', 'applications' => 'Gusaba akazi',
            'employer_area' => 'Abakoresha', 'admin_area' => 'Ubuyobozi', 'language' => 'Ururimi',
            'featured' => 'Byatoranijwe', 'remote' => 'Kure', 'new' => 'Gishya', 'deadline' => 'Itariki ntarengwa', 'location' => 'Aho kari',
            'salary' => 'Umushahara', 'category' => 'Icyiciro', 'published' => 'Yatangajwe', 'verified' => 'Yemejwe',
            'external' => 'Amahirwe yo hanze', 'details' => 'Reba ibisobanuro', 'contact' => 'Twandikire',
        ],
        'fr' => [
            'nav_home' => 'Accueil', 'nav_jobs' => 'Trouver un emploi', 'nav_companies' => 'Entreprises',
            'nav_opportunities' => 'Opportunités', 'nav_career' => 'Centre de carrière', 'nav_community' => 'Communauté',
            'nav_login' => 'Connexion', 'nav_register' => "S'inscrire", 'nav_post_job' => 'Publier une offre',
            'hero_title' => 'Votre prochaine opportunité commence ici.', 'hero_text' => 'Découvrez emplois, stages, télétravail et ressources de carrière. Connectez-vous aux employeurs et avancez.',
            'search_placeholder' => 'Titre, compétence ou mot-clé', 'location_placeholder' => 'Ville, province ou télétravail', 'search' => 'Rechercher',
            'view_jobs' => 'Voir les opportunités', 'learn_more' => 'En savoir plus', 'apply_now' => 'Postuler', 'save' => 'Enregistrer',
            'empty_title' => 'Rien pour le moment', 'empty_text' => 'Revenez bientôt — de nouvelles opportunités sont ajoutées chaque jour.',
            'no_results' => 'Aucune opportunité trouvée.', 'clear_filters' => 'Effacer les filtres',
            'results' => 'opportunités', 'view_all' => 'Tout voir', 'dashboard' => 'Tableau de bord',
            'logout' => 'Déconnexion', 'profile' => 'Mon profil', 'saved_jobs' => 'Offres enregistrées', 'applications' => 'Mes candidatures',
            'employer_area' => 'Espace employeur', 'admin_area' => 'Administration', 'language' => 'Langue',
            'featured' => 'En vedette', 'remote' => 'À distance', 'new' => 'Nouveau', 'deadline' => 'Date limite', 'location' => 'Lieu',
            'salary' => 'Salaire', 'category' => 'Catégorie', 'published' => 'Publié', 'verified' => 'Vérifié',
            'external' => 'Opportunité externe', 'details' => 'Voir les détails', 'contact' => 'Nous contacter',
        ],
    ];

    public static function boot(string $default, array $supported): void
    {
        self::$supported = $supported;
        $requested = (string) (($_GET['lang'] ?? '') ?: Session::get('lang') ?: $default);
        if (array_key_exists($requested, $supported)) {
            self::$locale = $requested;
            Session::put('lang', $requested);
        }
    }

    public static function locale(): string { return self::$locale; }
    public static function supported(): array { return self::$supported; }

    public static function translate(string $key, array $replace = []): string
    {
        $text = self::STRINGS[self::$locale][$key] ?? self::STRINGS['en'][$key] ?? $key;
        foreach ($replace as $search => $value) {
            $text = str_replace(':' . $search, (string) $value, $text);
        }
        return $text;
    }
}
