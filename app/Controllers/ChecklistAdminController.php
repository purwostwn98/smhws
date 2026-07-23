<?php

namespace App\Controllers;

use App\Models\ChecklistSectionModel;
use App\Models\ChecklistItemModel;

class ChecklistAdminController extends BaseController
{
    private function requireAdmin(): bool
    {
        return session()->get('is_logged_in') && session()->get('role') === 'admin';
    }

    /** GET /admin/checklist — daftar section */
    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->requireAdmin()) {
            return redirect()->to(base_url('login'));
        }

        $secModel = new ChecklistSectionModel();
        $itemModel = new ChecklistItemModel();

        $sections = $secModel->getAll();
        foreach ($sections as &$sec) {
            $sec['total_items']  = $itemModel->where('section_id', $sec['id'])->countAllResults();
            $sec['active_items'] = $itemModel->where('section_id', $sec['id'])->where('is_active', 1)->countAllResults();
        }
        unset($sec);

        return view('admin/checklist/index', ['sections' => $sections]);
    }

    /** GET /admin/checklist/{section_key} — item per section */
    public function sectionItems(string $sectionKey): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->requireAdmin()) {
            return redirect()->to(base_url('login'));
        }

        $secModel  = new ChecklistSectionModel();
        $itemModel = new ChecklistItemModel();

        $section = $secModel->byKey($sectionKey);
        if (! $section) {
            return redirect()->to(base_url('admin/checklist'))
                ->with('error', 'Section tidak ditemukan.');
        }

        $groups = $itemModel->adminGrouped((int) $section['id']);

        return view('admin/checklist/items', [
            'section' => $section,
            'groups'  => $groups,
        ]);
    }

    /** GET /admin/checklist/item/buat?section=stressor */
    public function itemBuat(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->requireAdmin()) {
            return redirect()->to(base_url('login'));
        }

        $secModel  = new ChecklistSectionModel();
        $itemModel = new ChecklistItemModel();

        $sections   = $secModel->getAll();
        $sectionKey = $this->request->getGet('section') ?? '';
        $section    = $sectionKey ? $secModel->byKey($sectionKey) : null;
        $subsections = $section ? $itemModel->getSubsections((int)$section['id']) : [];

        return view('admin/checklist/form_item', [
            'sections'    => $sections,
            'section'     => $section,
            'subsections' => $subsections,
            'item'        => null,
        ]);
    }

    /** POST /admin/checklist/item/simpan */
    public function itemSimpan(): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->requireAdmin()) {
            return redirect()->to(base_url('login'));
        }

        $post      = $this->request->getPost();
        $secModel  = new ChecklistSectionModel();
        $itemModel = new ChecklistItemModel();

        $section = $secModel->find((int)($post['section_id'] ?? 0));
        if (! $section) {
            return redirect()->back()->with('error', 'Section tidak valid.');
        }

        $subsectionKey   = trim($post['subsection_key'] ?? '');
        $subsectionLabel = trim($post['subsection_label'] ?? '');
        $isNewSub        = $subsectionKey === '__new__';

        if ($isNewSub) {
            $subsectionKey   = $this->slugify(trim($post['new_subsection_key'] ?? ''));
            $subsectionLabel = trim($post['new_subsection_label'] ?? '');
        }

        if (! $subsectionKey || ! $subsectionLabel) {
            return redirect()->back()->with('error', 'Subsection key dan label wajib diisi.');
        }

        // Urutan subsection: ambil max + 1 jika baru
        $subUrutan = (int)($post['subsection_urutan'] ?? 0);
        if (! $subUrutan) {
            $maxRow = $itemModel->db->table('checklist_items')
                ->selectMax('subsection_urutan')
                ->where('section_id', $section['id'])
                ->where('deleted_at IS NULL')
                ->get()->getRowArray();
            $subUrutan = ($maxRow['subsection_urutan'] ?? 0) + 1;
        }

        // Urutan item dalam subsection
        $maxItem = $itemModel->db->table('checklist_items')
            ->selectMax('item_urutan')
            ->where('section_id', $section['id'])
            ->where('subsection_key', $subsectionKey)
            ->where('deleted_at IS NULL')
            ->get()->getRowArray();
        $itemUrutan = ($maxItem['item_urutan'] ?? 0) + 1;

        $itemModel->insert([
            'section_id'        => $section['id'],
            'subsection_key'    => $subsectionKey,
            'subsection_label'  => $subsectionLabel,
            'subsection_urutan' => $subUrutan,
            'item_label'        => trim($post['item_label'] ?? ''),
            'input_type'        => in_array($post['input_type'] ?? '', ['checkbox','radio']) ? $post['input_type'] : 'checkbox',
            'item_urutan'       => $itemUrutan,
            'is_active'         => 1,
        ]);

        return redirect()->to(base_url('admin/checklist/' . $section['section_key']))
            ->with('success', 'Item berhasil ditambahkan.');
    }

    /** GET /admin/checklist/item/edit/{id} */
    public function itemEdit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->requireAdmin()) {
            return redirect()->to(base_url('login'));
        }

        $secModel  = new ChecklistSectionModel();
        $itemModel = new ChecklistItemModel();

        $item = $itemModel->find($id);
        if (! $item) {
            return redirect()->to(base_url('admin/checklist'))
                ->with('error', 'Item tidak ditemukan.');
        }

        $section     = $secModel->find((int)$item['section_id']);
        $sections    = $secModel->getAll();
        $subsections = $itemModel->getSubsections((int)$item['section_id']);

        return view('admin/checklist/form_item', [
            'sections'    => $sections,
            'section'     => $section,
            'subsections' => $subsections,
            'item'        => $item,
        ]);
    }

    /** POST /admin/checklist/item/update/{id} */
    public function itemUpdate(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->requireAdmin()) {
            return redirect()->to(base_url('login'));
        }

        $post      = $this->request->getPost();
        $itemModel = new ChecklistItemModel();
        $secModel  = new ChecklistSectionModel();

        $item = $itemModel->find($id);
        if (! $item) {
            return redirect()->to(base_url('admin/checklist'))
                ->with('error', 'Item tidak ditemukan.');
        }

        $subsectionKey   = trim($post['subsection_key'] ?? $item['subsection_key']);
        $subsectionLabel = trim($post['subsection_label'] ?? $item['subsection_label']);

        $itemModel->update($id, [
            'item_label'       => trim($post['item_label'] ?? ''),
            'subsection_label' => $subsectionLabel,
            'input_type'       => in_array($post['input_type'] ?? '', ['checkbox','radio']) ? $post['input_type'] : $item['input_type'],
            'is_active'        => isset($post['is_active']) ? 1 : 0,
        ]);

        $section = $secModel->find((int)$item['section_id']);
        return redirect()->to(base_url('admin/checklist/' . $section['section_key']))
            ->with('success', 'Item berhasil diperbarui.');
    }

    /** POST /admin/checklist/item/hapus/{id} */
    public function itemHapus(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->requireAdmin()) {
            return redirect()->to(base_url('login'));
        }

        $itemModel = new ChecklistItemModel();
        $secModel  = new ChecklistSectionModel();

        $item = $itemModel->find($id);
        if (! $item) {
            return redirect()->to(base_url('admin/checklist'))->with('error', 'Item tidak ditemukan.');
        }

        $section = $secModel->find((int)$item['section_id']);
        $itemModel->delete($id);

        return redirect()->to(base_url('admin/checklist/' . $section['section_key']))
            ->with('success', 'Item berhasil dihapus.');
    }

    /** POST /admin/checklist/item/toggle/{id} */
    public function itemToggle(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->requireAdmin()) {
            return redirect()->to(base_url('login'));
        }

        $itemModel = new ChecklistItemModel();
        $secModel  = new ChecklistSectionModel();

        $item = $itemModel->find($id);
        if (! $item) {
            return redirect()->to(base_url('admin/checklist'))->with('error', 'Item tidak ditemukan.');
        }

        $itemModel->update($id, ['is_active' => $item['is_active'] ? 0 : 1]);

        $section = $secModel->find((int)$item['section_id']);
        return redirect()->to(base_url('admin/checklist/' . $section['section_key']))
            ->with('success', 'Status item diperbarui.');
    }

    /** GET /admin/checklist/subsections?section_id=1 — AJAX: list subsections */
    public function subsectionsAjax(): \CodeIgniter\HTTP\ResponseInterface
    {
        $sectionId = (int)($this->request->getGet('section_id') ?? 0);
        $itemModel = new ChecklistItemModel();
        $subs      = $itemModel->getSubsections($sectionId);
        return $this->response->setJSON($subs);
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        return trim($text, '_');
    }
}
