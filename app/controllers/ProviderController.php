<?php
namespace App\Controllers;

class ProviderController extends BaseController {
    private $providerModel;

    public function __construct() {
        $this->providerModel = $this->model('Provider');
    }

    protected function requireAdmin() {
        if (!isLoggedIn() || !isAdmin()) {
            flash('error', 'Access denied. Admin only.');
            redirect('dashboard');
        }
    }

    public function index() {
        $this->requireAdmin();

        $data = [
            'title' => 'Manage Providers',
            'providers' => $this->providerModel->getAllProviders()
        ];
        $this->view('admin/providers', $data);
    }

    private function normalizeServiceSchema(?string $raw, array &$data): ?string
    {
        $raw = $raw === null ? null : trim($raw);

        if ($raw === null || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $data['service_schema_err'] = 'Service schema must be valid JSON.';
            return $raw; // keep what user typed for re-render
        }

        // Optional: pretty re-encode to keep DB consistent
        return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function create() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // IMPORTANT: do NOT sanitize the JSON schema with FULL_SPECIAL_CHARS
            $rawSchema = $_POST['service_schema'] ?? null;

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'base_url_template' => trim($_POST['base_url_template'] ?? ''),
                'documentation_url' => trim($_POST['documentation_url'] ?? ''),
                'service_schema' => null,
                'status' => isset($_POST['status']) && $_POST['status'] == 'active' ? 'active' : 'inactive',
                'name_err' => '',
                'base_url_template_err' => '',
                'service_schema_err' => ''
            ];

            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter provider name';
            }

            if (!empty($data['base_url_template']) && !filter_var($data['base_url_template'], FILTER_VALIDATE_URL)) {
                $data['base_url_template_err'] = 'Please enter a valid URL';
            }

            $data['service_schema'] = $this->normalizeServiceSchema(
                is_string($rawSchema) ? $rawSchema : null,
                $data
            );

            if (empty($data['name_err']) && empty($data['base_url_template_err']) && empty($data['service_schema_err'])) {
                if ($this->providerModel->createProvider($data)) {
                    flash('success', 'Provider added successfully!');
                    redirect('admin/providers');
                } else {
                    flash('error', 'Failed to add provider', 'alert alert-danger');
                    $this->view('admin/provider_create', $data);
                }
            } else {
                $this->view('admin/provider_create', $data);
            }
        } else {
            $data = [
                'title' => 'Add Provider',
                'name' => '',
                'description' => '',
                'base_url_template' => '',
                'documentation_url' => '',
                'service_schema' => '',
                'status' => 'active',
                'name_err' => '',
                'base_url_template_err' => '',
                'service_schema_err' => ''
            ];
            $this->view('admin/provider_create', $data);
        }
    }

    public function edit($id = null) {
        $this->requireAdmin();

        if (!$id) {
            flash('error', 'Provider ID is required');
            redirect('admin/providers');
        }

        $provider = $this->providerModel->getProviderById($id);
        if (!$provider) {
            flash('error', 'Provider not found');
            redirect('admin/providers');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $rawSchema = $_POST['service_schema'] ?? null;

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id' => $id,
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'base_url_template' => trim($_POST['base_url_template'] ?? ''),
                'documentation_url' => trim($_POST['documentation_url'] ?? ''),
                'service_schema' => null,
                'status' => isset($_POST['status']) && $_POST['status'] == 'active' ? 'active' : 'inactive',
                'name_err' => '',
                'base_url_template_err' => '',
                'service_schema_err' => ''
            ];

            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter provider name';
            }

            if (!empty($data['base_url_template']) && !filter_var($data['base_url_template'], FILTER_VALIDATE_URL)) {
                $data['base_url_template_err'] = 'Please enter a valid URL';
            }

            $data['service_schema'] = $this->normalizeServiceSchema(
                is_string($rawSchema) ? $rawSchema : null,
                $data
            );

            if (empty($data['name_err']) && empty($data['base_url_template_err']) && empty($data['service_schema_err'])) {
                if ($this->providerModel->updateProvider($id, $data)) {
                    flash('success', 'Provider updated successfully!');
                    redirect('admin/providers');
                } else {
                    flash('error', 'Failed to update provider', 'alert alert-danger');
                    $this->view('admin/provider_edit', $data);
                }
            } else {
                $this->view('admin/provider_edit', $data);
            }
        } else {
            $data = [
                'title' => 'Edit Provider',
                'id' => $provider->id,
                'name' => $provider->name,
                'description' => $provider->description,
                'base_url_template' => $provider->base_url_template,
                'documentation_url' => $provider->documentation_url,
                'service_schema' => $provider->service_schema,
                'status' => $provider->status,
                'name_err' => '',
                'base_url_template_err' => '',
                'service_schema_err' => ''
            ];
            $this->view('admin/provider_edit', $data);
        }
    }

    public function delete($id = null) {
        $this->requireAdmin();

        if (!$id) {
            flash('error', 'Provider ID is required');
            redirect('admin/providers');
        }

        if ($this->providerModel->deleteProvider($id)) {
            flash('success', 'Provider deleted successfully!');
        } else {
            flash('error', 'Failed to delete provider', 'alert alert-danger');
        }
        redirect('admin/providers');
    }
}
