<?php
namespace App\Controllers;

class APIController extends BaseController
{
    private $instanceModel;
    private $providerModel;
    private $serviceModel;

    public function __construct()
    {
        $this->instanceModel = $this->model('APIInstance');
        $this->providerModel = $this->model('Provider');
        $this->serviceModel  = $this->model('APIService');
    }

    protected function requireAdmin()
    {
        if (!isLoggedIn() || !isAdmin()) {
            flash('error', 'Access denied. Admin only.');
            redirect('dashboard');
        }
    }

    /* =========================
       API INSTANCES
    ========================= */

    public function index()
    {
        $this->requireAdmin();

        $this->view('admin/api', [
            'title'     => 'API Instances',
            'instances' => $this->instanceModel->getAllInstances()
        ]);
    }

    public function create()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'provider_id' => (int)($_POST['provider_id'] ?? 0),
                'name'        => trim($_POST['name'] ?? ''),
                'base_url'    => trim($_POST['base_url'] ?? ''),
                'api_key'     => trim($_POST['api_key'] ?? ''),
                'default_markup' => (float)($_POST['default_markup'] ?? 0.00),
                'default_country' => trim($_POST['default_country'] ?? ''),
                'default_markup' => (float)($_POST['default_markup'] ?? 0.00),
                'status'      => ($_POST['status'] ?? 'active') === 'active' ? 'active' : 'inactive'
            ];

            if ($this->instanceModel->createInstance($data)) {
                flash('success', 'API instance added successfully!');
                redirect('api');
            }

            flash('error', 'Failed to add API instance', 'alert alert-danger');
        }

        $this->view('admin/api_create', [
            'title'     => 'Add API Instance',
            'providers' => $this->providerModel->getActiveProviders()
        ]);
    }

    public function logs()
    {
        $this->requireAdmin();

        $this->view('admin/api_logs', [
            'title' => 'API Logs'
        ]);
    }

    public function edit($id)
    {
        $this->requireAdmin();

        $instance = $this->instanceModel->getInstanceById((int)$id);
        if (!$instance) {
            flash('error', 'API instance not found', 'alert alert-danger');
            redirect('api');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'provider_id' => (int)($_POST['provider_id'] ?? $instance->provider_id),
                'name'        => trim($_POST['name'] ?? ''),
                'base_url'    => trim($_POST['base_url'] ?? ''),
                'api_key'     => trim($_POST['api_key'] ?? ''),
                'default_markup' => (float)($_POST['default_markup'] ?? 0.00),
                'default_country' => trim($_POST['default_country'] ?? ''),
                'default_markup' => (float)($_POST['default_markup'] ?? 0.00),
                'status'      => ($_POST['status'] ?? 'active') === 'active' ? 'active' : 'inactive'
            ];

            if ($this->instanceModel->updateInstance((int)$id, $data)) {
                flash('success', 'API instance updated successfully');
                redirect('api');
            }

            flash('error', 'Failed to update API instance', 'alert alert-danger');
        }

        $this->view('admin/api_edit', [
            'title'     => 'Edit API Instance',
            'instance'  => $instance,
            'providers' => $this->providerModel->getActiveProviders()
        ]);
    }

    public function delete($id)
    {
        $this->requireAdmin();

        if ($this->instanceModel->deleteInstance((int)$id)) {
            flash('success', 'API instance deleted successfully');
        } else {
            flash('error', 'Cannot delete instance with existing services. Remove services first.', 'alert alert-danger');
        }

        redirect('api');
    }

    /* =========================
       SERVICE SYNC
    ========================= */

    public function syncServices($instanceId)
    {
        $this->requireAdmin();

        $instanceId = (int)$instanceId;

        $instance = $this->instanceModel->getInstanceById($instanceId);
        if (!$instance) {
            flash('error', 'API instance not found', 'alert alert-danger');
            redirect('api');
        }

        $provider = $this->providerModel->getProviderById($instance->provider_id);
        if (!$provider || empty($provider->service_schema)) {
            flash('error', 'Provider service schema not configured', 'alert alert-danger');
            redirect('api');
        }

        $schema = json_decode($provider->service_schema, true);
        if (!is_array($schema) || empty($schema['services'])) {
            flash('error', 'Invalid provider service schema', 'alert alert-danger');
            redirect('api');
        }

        /* -------------------------
           1) SERVICES SYNC
        --------------------------*/
        $svcSchema = (array)$schema['services'];

        $svcEndpoint  = (string)($svcSchema['endpoint'] ?? '');
        $svcMethod    = strtoupper((string)($svcSchema['method'] ?? 'GET'));
        $svcPayload   = (array)($svcSchema['payload'] ?? []);
        $svcAuthParam = (string)($svcSchema['auth_param'] ?? 'key');
        $svcRespType  = (string)($svcSchema['response_type'] ?? '');
        $svcMap       = (array)($svcSchema['map'] ?? []);
        $svcDefaults  = (array)($svcSchema['defaults'] ?? []);

        $svcPayload[$svcAuthParam] = (string)$instance->api_key;
        $svcUrl = rtrim((string)$instance->base_url, '/') . $svcEndpoint;

        $svcResp = $this->callExternalAPI($svcUrl, $svcMethod, $svcPayload);
        $svcList = $this->normalizeResponseToList($svcResp, $svcRespType);

        if (!is_array($svcList)) {
            flash('error', 'Failed to fetch services from provider', 'alert alert-danger');
            redirect('api');
        }

        // Get instance default markup for pricing calculation
        $defaultMarkup = (float)($instance->default_markup ?? 0.00);

        $synced = 0;
        foreach ($svcList as $key => $raw) {
            if (!is_array($raw)) continue;

            $idKey   = $svcMap['id'] ?? 'id';
            $nameKey = $svcMap['name'] ?? 'name';

            $externalId = null;
            if ($idKey === '__key') {
                $externalId = (string)$key;
            } else {
                $externalId = isset($raw[$idKey]) ? (string)$raw[$idKey] : null;
            }

            if (!$externalId) continue;

            $name = isset($raw[$nameKey]) ? (string)$raw[$nameKey] : 'Service';

            $category = $svcDefaults['category'] ?? ((isset($svcMap['category']) && $svcMap['category'] && isset($raw[$svcMap['category']])) ? $raw[$svcMap['category']] : '');
            $type     = $svcDefaults['type'] ?? ((isset($svcMap['type']) && $svcMap['type'] && isset($raw[$svcMap['type']])) ? $raw[$svcMap['type']] : '');
            $min      = isset($svcMap['min']) && isset($raw[$svcMap['min']]) ? (int)$raw[$svcMap['min']] : (int)($svcDefaults['min'] ?? 1);
            $max      = isset($svcMap['max']) && isset($raw[$svcMap['max']]) ? (int)$raw[$svcMap['max']] : (int)($svcDefaults['max'] ?? 100000);

            // Some providers return price in services list; SMS-Man pricing is separate.
            $rate = 0.0;
            if (isset($svcMap['price']) && $svcMap['price'] && isset($raw[$svcMap['price']]) && is_numeric($raw[$svcMap['price']])) {
                $rate = (float)$raw[$svcMap['price']];
            }

            // Apply instance default markup during sync
            $this->serviceModel->upsertService([
                'api_instance_id'     => $instanceId,
                'external_service_id' => $externalId,
                'name'                => $name,
                'category'            => (string)$category,
                'type'                => (string)$type,
                'api_rate'            => $rate,
                'markup'              => $defaultMarkup,
                'final_price'         => $rate + $defaultMarkup,
                'min_qty'             => $min,
                'max_qty'             => $max,
                'extra'               => $raw
            ]);

            $synced++;
        }

        /* -------------------------
           2) PRICING SYNC (optional)
        --------------------------*/
        $pricingUpdated = 0;

        if (!empty($schema['pricing'])) {
            if (empty($instance->default_country)) {
                flash('error', 'Pricing sync skipped: default_country is not set for this API instance.', 'alert alert-danger');
            } else {
                $pSchema   = (array)$schema['pricing'];
                $pEndpoint = (string)($pSchema['endpoint'] ?? '');
                $pMethod   = strtoupper((string)($pSchema['method'] ?? 'GET'));
                $pAuth     = (string)($pSchema['auth_param'] ?? 'key');
                $pPayload  = (array)($pSchema['payload'] ?? []);
                $pMap      = (array)($pSchema['map'] ?? []);
                $pRespType = (string)($pSchema['response_type'] ?? '');

                // Replace placeholders
                foreach ($pPayload as $k => $v) {
                    if (!is_string($v)) continue;
                    $pPayload[$k] = str_replace('{default_country}', (string)$instance->default_country, $v);
                }

                $pPayload[$pAuth] = (string)$instance->api_key;
                $pUrl = rtrim((string)$instance->base_url, '/') . $pEndpoint;

                $pResp = $this->callExternalAPI($pUrl, $pMethod, $pPayload);
                $pricingAssoc = $this->normalizeResponseToKeyedObject($pResp, $pRespType);

                if (is_array($pricingAssoc)) {
                    $rateField = $pMap['rate'] ?? 'cost';

                    $rates = [];
                    foreach ($pricingAssoc as $serviceKey => $row) {
                        $serviceId = (string)$serviceKey; // __key
                        if ($serviceId === '') continue;

                        // Providers may return either an object per service or a scalar numeric price.
                        if (is_array($row)) {
                            if (isset($row[$rateField]) && is_numeric($row[$rateField])) {
                                $rates[$serviceId] = (float)$row[$rateField];
                            }
                        } else {
                            if (is_numeric($row)) {
                                $rates[$serviceId] = (float)$row;
                            }
                        }
                    }

                    if ($rates) {
                        $pricingUpdated = $this->serviceModel->updateRatesFromPricing($instanceId, $rates);
                    }
                }
            }
        }

        /* -------------------------
           3) APPLY DEFAULT MARKUP TO ALL SERVICES
        --------------------------*/
        $markupApplied = 0;
        if ($defaultMarkup > 0) {
            $markupApplied = $this->serviceModel->applyDefaultMarkup($instanceId, $defaultMarkup);
        }

        // Build success message
        $messages = ["Service sync completed. {$synced} services processed."];
        if ($pricingUpdated > 0) {
            $messages[] = "Pricing updated for {$pricingUpdated} services.";
        }
        if ($markupApplied > 0) {
            $messages[] = "Default markup (${$defaultMarkup}) applied to {$markupApplied} services.";
           3) APPLY DEFAULT MARKUP
        --------------------------*/
        $defaultMarkup = (float)($instance->default_markup ?? 0.00);
        if ($defaultMarkup > 0.0) {
            $markupApplied = $this->instanceModel->applyDefaultMarkupToServices($instanceId);
            if ($pricingUpdated > 0) {
                flash('success', "Service sync completed. {$synced} services processed. Pricing updated for {$pricingUpdated} services. Default markup ({$defaultMarkup}) applied to {$markupApplied} services.");
            } else {
                flash('success', "Service sync completed. {$synced} services processed. Default markup ({$defaultMarkup}) applied to {$markupApplied} services.");
            }
        } else {
            if ($pricingUpdated > 0) {
                flash('success', "Service sync completed. {$synced} services processed. Pricing updated for {$pricingUpdated} services.");
            } else {
                flash('success', "Service sync completed. {$synced} services processed.");
            }
        }

        flash('success', implode(' ', $messages));

        redirect('api');
    }

    private function normalizeResponseToList($response, string $responseType)
    {
        if (!is_array($response)) {
            return null;
        }

        // Wrapped data
        if (isset($response['data']) && is_array($response['data'])) {
            $response = $response['data'];
        }

        if ($responseType === 'keyed_object') {
            return $response;
        }

        return $response;
    }

    private function normalizeResponseToKeyedObject($response, string $responseType)
    {
        if (!is_array($response)) {
            return null;
        }

        if (isset($response['data']) && is_array($response['data'])) {
            $response = $response['data'];
        }

        if ($responseType === 'keyed_object') {
            return $response;
        }

        return null;
    }

    /* =========================
       API SERVICES (Manage)
    ========================= */

    public function services($instanceId = null)
    {
        $this->requireAdmin();

        $instanceId = (int)$instanceId;
        if ($instanceId <= 0) {
            flash('error', 'API instance ID is required.', 'alert alert-danger');
            redirect('api');
        }

        $instance = $this->instanceModel->getInstanceById($instanceId);
        if (!$instance) {
            flash('error', 'API instance not found', 'alert alert-danger');
            redirect('api');
        }

        // Optional search & pagination
        $q = trim($_GET['q'] ?? '');
        $perPage = (int)($_GET['per_page'] ?? 50);
        if ($perPage < 10) $perPage = 10;
        if ($perPage > 200) $perPage = 200;

        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;

        $total = $this->serviceModel->countByInstanceFiltered($instanceId, $q);
        $totalPages = max(1, (int)ceil($total / $perPage));
        if ($page > $totalPages) $page = $totalPages;

        $offset = ($page - 1) * $perPage;

        $services = $this->serviceModel->getByInstancePaginated($instanceId, $q, $perPage, $offset);

        $this->view('admin/api_services', [
            'title'      => 'Manage Services - ' . h($instance->name),
            'instance'   => $instance,
            'services'   => $services,
            'filters'    => ['q' => $q, 'per_page' => $perPage],
            'pagination' => ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'total_pages' => $totalPages]
        ]);
    }

    public function updateService()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('api');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $id      = (int)($_POST['id'] ?? 0);
        $markup  = (float)($_POST['markup'] ?? 0);
        $status  = $_POST['status'] ?? 'inactive';
        $visible = $_POST['visible'] ?? 'no';

        if ($id > 0) {
            if ($this->serviceModel->updateAdminSettings($id, $markup, $status, $visible)) {
                flash('success', 'Service updated successfully');
            } else {
                flash('error', 'Failed to update service', 'alert alert-danger');
            }
        }

        $service = $this->serviceModel->getById($id);
        if ($service) {
            redirect('api/services/' . (int)$service->api_instance_id);
        }

        redirect('api');
    }

    public function bulkUpdateServices()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('api');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // CSRF protection (bulk actions change data)
        if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            flash('error', 'Invalid CSRF token.', 'alert alert-danger');
            $fallbackInstance = (int)($_POST['instance_id'] ?? 0);
            redirect($fallbackInstance > 0 ? ('api/services/' . $fallbackInstance) : 'api');
        }

        $instanceId = (int)($_POST['instance_id'] ?? 0);
        $idsCsv     = trim((string)($_POST['ids_csv'] ?? ''));
        $action     = trim((string)($_POST['bulk_action'] ?? ''));

        if ($instanceId <= 0 || $idsCsv === '' || $action === '') {
            flash('error', 'Select at least one service and a bulk action.', 'alert alert-danger');
            redirect('api/services/' . $instanceId);
        }

        $ids = array_filter(array_map('intval', preg_split('/\s*,\s*/', $idsCsv)), fn($v) => $v > 0);

        if (empty($ids)) {
            flash('error', 'No valid services selected.', 'alert alert-danger');
            redirect('api/services/' . $instanceId);
        }

        $status = null;
        $visible = null;
        $markup = null;
        $doPublish = false;

        switch ($action) {
            case 'status_active':
                $status = 'active';
                break;
            case 'status_inactive':
                $status = 'inactive';
                break;
            case 'visible_yes':
                $visible = 'yes';
                break;
            case 'visible_no':
                $visible = 'no';
                break;
            case 'set_markup':
                $markup = (float)($_POST['bulk_markup'] ?? 0);
                break;
            case 'publish_products':
                $doPublish = true;
                break;
            default:
                flash('error', 'Invalid bulk action.', 'alert alert-danger');
                redirect('api/services/' . $instanceId);
        }

        if ($doPublish) {
            $count = $this->serviceModel->publishServicesToProducts($instanceId, $ids);
            if ($count > 0) {
                flash('success', "Published {$count} selected services to Products.");
            } else {
                flash('error', 'Publish failed or no services published.', 'alert alert-danger');
            }
        } else {
            $ok = $this->serviceModel->bulkUpdateByInstance($instanceId, $ids, $status, $visible, $markup);

            if ($ok) {
                flash('success', 'Bulk update applied to selected services.');
            } else {
                flash('error', 'Bulk update failed.', 'alert alert-danger');
            }
        }

        // Preserve list state if provided
        $qs = [];
        $q = trim((string)($_POST['q'] ?? ''));
        $perPage = (int)($_POST['per_page'] ?? 0);
        $page = (int)($_POST['page'] ?? 0);
        if ($q !== '') $qs['q'] = $q;
        if ($perPage > 0) $qs['per_page'] = $perPage;
        if ($page > 0) $qs['page'] = $page;

        $path = 'api/services/' . $instanceId;
        if (!empty($qs)) {
            $path .= '?' . http_build_query($qs);
        }

        redirect($path);
    }

    /**
     * Apply bulk markup to ALL services of an API instance.
     * POST /api/applyBulkMarkup
     *
     * @param int|null $instanceId (optional, for direct URL access)
     */
    public function applyBulkMarkup($instanceId = null)
    {
        $this->requireAdmin();

        // Get instance_id from POST body or route parameter
        $instanceId = (int)($instanceId ?? ($_POST['instance_id'] ?? 0));

        if ($instanceId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'API instance ID is required.']);
            return;
        }

        // Get the instance
        $instance = $this->instanceModel->getInstanceById($instanceId);
        if (!$instance) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'API instance not found.']);
            return;
        }

        // Validate input
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson = stripos($contentType, 'application/json') !== false;

        $markup = 0.00;

        if ($isJson) {
            // JSON payload
            $rawBody = file_get_contents('php://input');
            $payload = json_decode($rawBody ?: '{}', true);
            $markup = (float)($payload['markup'] ?? 0.00);
        } else {
            // Form payload
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $markup = (float)($_POST['markup'] ?? 0.00);
        }

        // Validate markup range
        if ($markup < 0) {
            $markup = 0.00;
        }
        if ($markup > 1000.00) {
            $markup = 1000.00;
        }

        // Apply bulk markup
        $updatedCount = $this->serviceModel->bulkUpdateMarkup($instanceId, $markup);

        // Update instance default markup if requested
        $updateDefault = (bool)($_POST['update_default'] ?? ($payload['update_default'] ?? false));
        if ($updateDefault) {
            $this->instanceModel->setDefaultMarkup($instanceId, $markup);
        }

        // Return JSON response
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'message' => "Markup applied to {$updatedCount} services.",
                'data' => [
                    'instance_id' => $instanceId,
                    'markup' => round($markup, 2),
                    'updated_count' => $updatedCount,
                    'updated_default' => $updateDefault
                ]
            ]);
            return;
        }

        // HTML response
        flash('success', "Bulk markup applied to {$updatedCount} services.");
        redirect('api/services/' . $instanceId);
    }

    /**
     * Save instance settings including default markup.
     * POST /api/instanceSettings
     */
    public function instanceSettings()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('api');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $instanceId = (int)($_POST['instance_id'] ?? 0);

        if ($instanceId <= 0) {
            flash('error', 'Invalid API instance.', 'alert alert-danger');
     * Apply bulk markup to ALL services in an instance
     */
    public function applyBulkMarkup()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // CSRF protection
        if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
            return;
        }

        $instanceId = (int)($_POST['instance_id'] ?? 0);
        $markup = (float)($_POST['markup_percentage'] ?? 0);

        if ($instanceId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid instance ID']);
            return;
        }

        $count = $this->serviceModel->bulkUpdateMarkup($instanceId, $markup);

        echo json_encode([
            'status' => 'success',
            'updated_count' => $count,
            'message' => "Updated {$count} services with markup {$markup}"
        ]);
    }

    /**
     * Instance settings - manage default_markup
     */
    public function instanceSettings($id = null)
    {
        $this->requireAdmin();

        $instanceId = (int)$id;
        if ($instanceId <= 0) {
            flash('error', 'Invalid instance ID', 'alert alert-danger');
            redirect('api');
        }

        $instance = $this->instanceModel->getInstanceById($instanceId);
        if (!$instance) {
            flash('error', 'API instance not found.', 'alert alert-danger');
            redirect('api');
        }

        // Update default markup
        $defaultMarkup = (float)($_POST['default_markup'] ?? 0.00);
        if ($defaultMarkup < 0) $defaultMarkup = 0.00;
        if ($defaultMarkup > 1000.00) $defaultMarkup = 1000.00;

        // Apply to all services if requested
        $applyToAll = isset($_POST['apply_to_all']);

        $this->instanceModel->setDefaultMarkup($instanceId, $defaultMarkup);

        if ($applyToAll) {
            $updatedCount = $this->serviceModel->applyDefaultMarkup($instanceId, $defaultMarkup);
            flash('success', "Default markup set to {$defaultMarkup}. Applied to {$updatedCount} services.");
        } else {
            flash('success', "Default markup updated to {$defaultMarkup}.");
        }

        redirect('api/services/' . $instanceId);
            flash('error', 'API instance not found', 'alert alert-danger');
            redirect('api');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // CSRF protection
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('api/instanceSettings/' . $instanceId);
            }

            $action = trim($_POST['action'] ?? '');
            $defaultMarkup = (float)($_POST['default_markup'] ?? 0);

            switch ($action) {
                case 'update_default':
                    $this->instanceModel->setDefaultMarkup($instanceId, $defaultMarkup);
                    flash('success', 'Default markup updated successfully');
                    break;

                case 'apply_to_all':
                    $count = $this->instanceModel->applyDefaultMarkupToServices($instanceId);
                    flash('success', "Applied default markup to {$count} services");
                    break;

                case 'clear_markup':
                    $count = $this->serviceModel->bulkUpdateMarkup($instanceId, 0.00);
                    flash('success', "Cleared markup for {$count} services");
                    break;

                default:
                    flash('error', 'Invalid action', 'alert alert-danger');
            }

            redirect('api/instanceSettings/' . $instanceId);
        }

        // GET request - show form
        $this->view('admin/api_instance_settings', [
            'title' => 'Instance Settings - ' . h($instance->name),
            'instance' => $instance
        ]);
    }

    /* =========================
       TEST CONNECTION
    ========================= */

    /**
     * Test an API connection.
     *
     * Supports:
     * - AJAX POST (JSON) from api_create view: /api/test
     * - Optional page view: /api/test/{id}
     */
    public function test($id = null)
    {
        $this->requireAdmin();

        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $isJson = stripos($contentType, 'application/json') !== false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isJson) {
            header('Content-Type: application/json; charset=utf-8');

            $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            $rawBody = file_get_contents('php://input');
            $payload = json_decode($rawBody ?: '{}', true);

            $csrfBody = is_array($payload) ? ($payload['csrf_token'] ?? '') : '';
            $csrfToken = trim($csrfHeader ?: $csrfBody);

            if ($csrfToken === '' || !verify_csrf_token($csrfToken)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
                return;
            }

            $baseUrl = trim((string)($payload['base_url'] ?? ''));
            $apiKey  = trim((string)($payload['api_key'] ?? ''));

            if ($baseUrl === '' || $apiKey === '') {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Base URL and API key are required.']);
                return;
            }

            if (!filter_var($baseUrl, FILTER_VALIDATE_URL) || !preg_match('~^https?://~i', $baseUrl)) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Base URL must be a valid http(s) URL.']);
                return;
            }

            // Lightweight reachability probe.
            $probeUrl = rtrim($baseUrl, '/') . '/';

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $probeUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_HTTPHEADER     => ['Accept: application/json, text/plain, */*'],
            ]);

            $resp = curl_exec($ch);
            $err  = curl_error($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($resp === false) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $err ?: 'Connection failed.']);
                return;
            }

            if ($code >= 400 && in_array($code, [401, 403], true)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Provider responded but authentication may be invalid (HTTP ' . $code . ').'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Connection OK (HTTP ' . ($code ?: 200) . ').'
            ]);
            return;
        }

        if ($id === null) {
            flash('error', 'API ID is required for this page view.', 'alert alert-danger');
            redirect('api');
        }

        $this->view('admin/api_test', [
            'title'  => 'Test API',
            'api_id' => (int)$id
        ]);
    }

    /* =========================
       UTIL
    ========================= */

    private function callExternalAPI(string $url, string $method, array $payload)
    {
        $ch = curl_init();

        if ($method === 'GET') {
            $url .= '?' . http_build_query($payload);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json']
        ]);

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
