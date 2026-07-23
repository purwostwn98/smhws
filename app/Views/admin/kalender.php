<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Kalender Konseling<?= $this->endSection() ?>

<?= $this->section('extra_css') ?>
<style>
  /* ── FullCalendar overrides ───────────────────────────── */
  #kalender-smhws {
    font-family: inherit;
  }
  #kalender-smhws .fc-toolbar-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1a2b40;
  }
  #kalender-smhws .fc-button-primary {
    background-color: #1a2b40 !important;
    border-color:     #1a2b40 !important;
    font-size: .8rem;
  }
  #kalender-smhws .fc-button-primary:hover,
  #kalender-smhws .fc-button-primary.fc-button-active {
    background-color: #243a55 !important;
    border-color:     #243a55 !important;
  }
  #kalender-smhws .fc-button-primary:focus {
    box-shadow: none !important;
  }
  #kalender-smhws .fc-daygrid-day-number,
  #kalender-smhws .fc-col-header-cell-cushion {
    color: #344054;
    text-decoration: none;
    font-size: .82rem;
  }
  #kalender-smhws .fc-daygrid-day.fc-day-today {
    background: rgba(59,130,246,.07) !important;
  }
  #kalender-smhws .fc-event {
    cursor: pointer;
    font-size: .78rem;
    padding: 1px 4px;
    border-radius: 4px;
  }
  #kalender-smhws .fc-event:hover {
    filter: brightness(.9);
  }
  #kalender-smhws .fc-list-event:hover td {
    background: #f8fafc;
    cursor: pointer;
  }
  /* tooltip */
  .fc-event-tooltip {
    position: fixed;
    z-index: 9999;
    background: #1e293b;
    color: #f8fafc;
    font-size: .78rem;
    padding: 6px 10px;
    border-radius: 6px;
    pointer-events: none;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0,0,0,.25);
    display: none;
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$statusLabel = [
  'menunggu'     => 'Menunggu',
  'dikonfirmasi' => 'Dikonfirmasi',
  'terjadwal'    => 'Terjadwal',
  'berlangsung'  => 'Berlangsung',
  'selesai'      => 'Selesai',
  'dibatalkan'   => 'Dibatalkan',
];
$statusColor = [
  'menunggu'     => '#f59e0b',
  'dikonfirmasi' => '#00bad1',
  'terjadwal'    => '#7367f0',
  'berlangsung'  => '#22c55e',
  'selesai'      => '#94a3b8',
  'dibatalkan'   => '#ef4444',
];
?>

<!-- Header -->
<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Kalender Konseling</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
      Jadwal sesi konseling – tampilan bulanan &amp; mingguan.
    </p>
  </div>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <a href="<?= base_url('admin/janji') ?>" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" style="font-size:.8rem;">
      <i class="ti tabler-list" style="font-size:1rem;"></i> Daftar
    </a>
    <span class="badge bg-label-secondary" style="font-size:.8rem;">
      <i class="ti tabler-calendar me-1"></i><?= date('d F Y') ?>
    </span>
  </div>
</div>

<!-- Legenda status -->
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <span class="text-muted me-1" style="font-size:.78rem;">Status:</span>
  <?php foreach ($statusLabel as $key => $label): ?>
  <span class="d-inline-flex align-items-center gap-1" style="font-size:.78rem;">
    <span style="width:10px;height:10px;border-radius:50%;background:<?= $statusColor[$key] ?>;display:inline-block;"></span>
    <?= $label ?>
  </span>
  <?php endforeach ?>
</div>

<!-- Kalender -->
<div class="card shadow-sm border-0">
  <div class="card-body p-3 p-md-4">
    <div id="kalender-smhws"></div>
  </div>
</div>

<!-- Tooltip element (reused) -->
<div id="fc-tooltip" class="fc-event-tooltip"></div>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
<script>
(function () {
  const EVENTS_URL = '<?= base_url('admin/kalender/events') ?>';
  const tooltip    = document.getElementById('fc-tooltip');

  const statusLabel = {
    menunggu:     'Menunggu',
    dikonfirmasi: 'Dikonfirmasi',
    terjadwal:    'Terjadwal',
    berlangsung:  'Berlangsung',
    selesai:      'Selesai',
    dibatalkan:   'Dibatalkan',
  };

  const cal = new FullCalendar.Calendar(document.getElementById('kalender-smhws'), {
    locale: 'id',
    initialView: 'dayGridMonth',
    height: 'auto',
    firstDay: 1,           // Senin
    navLinks: true,
    dayMaxEvents: 4,       // +more link setelah 4 event
    headerToolbar: {
      left:   'prev,next today',
      center: 'title',
      right:  'dayGridMonth,timeGridWeek,listWeek',
    },
    views: {
      dayGridMonth: { buttonText: 'Bulan' },
      timeGridWeek: { buttonText: 'Minggu' },
      listWeek:     { buttonText: 'Daftar' },
    },
    events: EVENTS_URL,

    /* Klik event → navigasi ke detail */
    eventClick: function (info) {
      info.jsEvent.preventDefault();
      if (info.event.url) window.location.href = info.event.url;
    },

    /* Klik tanggal di month view → pindah ke week view */
    navLinkDayClick: function (date) {
      cal.changeView('timeGridWeek', date);
    },

    /* Tooltip saat hover */
    eventMouseEnter: function (info) {
      const p   = info.event.extendedProps;
      const lbl = statusLabel[p.status] ?? p.status;
      const nim = p.nim      ? 'NIM: ' + p.nim + ' · ' : '';
      const ks  = p.konselor ? p.konselor : '';
      const jam = info.event.start
        ? info.event.start.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB · '
        : '';
      tooltip.innerHTML = info.event.title + ' · ' + nim + jam + lbl 
        + (ks ? '<br>' + ks : '');
      tooltip.style.display = 'block';
    },
    eventMouseLeave: function () {
      tooltip.style.display = 'none';
    },

    /* Konten event kustom di month/week view */
    eventContent: function (arg) {
      const p   = arg.event.extendedProps;
      const lbl = statusLabel[p.status] ?? '';
      if (arg.view.type === 'listWeek') return; // pakai default list
      const dot = '<span style="width:6px;height:6px;border-radius:50%;background:#fff;display:inline-block;margin-right:4px;opacity:.85;flex-shrink:0;"></span>';
      const name = '<span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + arg.event.title + '</span>';
      return {
        html: '<div style="display:flex;align-items:center;width:100%;overflow:hidden;">' + dot + name + '</div>',
      };
    },

    noEventsContent: 'Tidak ada jadwal pada periode ini.',
    loading: function (isLoading) {
      document.getElementById('kalender-smhws').style.opacity = isLoading ? '.5' : '1';
    },
  });

  cal.render();

  /* Gerakkan tooltip mengikuti kursor */
  document.addEventListener('mousemove', function (e) {
    tooltip.style.left = (e.clientX + 14) + 'px';
    tooltip.style.top  = (e.clientY - 32) + 'px';
  });
})();
</script>
<?= $this->endSection() ?>
