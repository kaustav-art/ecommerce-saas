            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl">
                <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                  <div class="mb-2 mb-md-0">
                    © <?= date('Y'); ?> Modave eCommerce Admin Panel. All rights reserved.
                  </div>
                  <div class="d-none d-lg-inline-block">
                    <span class="badge bg-label-success">CodeIgniter 3 MVC</span>
                    <span class="badge bg-label-info ms-2">RBAC System</span>
                  </div>
                </div>
              </div>
            </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>

      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="<?= base_url('assets/vendor/libs/jquery/jquery.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/libs/popper/popper.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/js/bootstrap.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/libs/node-waves/node-waves.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/libs/sweetalert2/sweetalert2.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/js/menu.js'); ?>"></script>

    <!-- Main JS -->
    <script src="<?= base_url('assets/js/main.js'); ?>"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        (function () {
          if (typeof jQuery === 'undefined' || !jQuery.fn || !jQuery.fn.DataTable) return;
          var $ = jQuery;

        $('.card table.table').each(function () {
          var $tbl = $(this);
          if ($tbl.hasClass('no-datatable') || $.fn.DataTable.isDataTable(this)) return;
          if ($tbl.find('thead th').length === 0) return;

          // Hide redundant manual search bars inside the same card if DataTable provides live search
          var $tbody = $tbl.find('tbody');
          var emptyMsg = 'No matching records found';

          // Remove placeholder <td colspan="..."> row before DataTables initializes so column counts match
          var $rows = $tbody.find('tr');
          if ($rows.length === 1) {
            var $firstTd = $rows.first().find('td');
            if ($firstTd.length === 1 && $firstTd.attr('colspan')) {
              emptyMsg = $.trim($firstTd.text()) || emptyMsg;
              $rows.remove();
            }
          }

          var nonOrderable = [];
          $tbl.find('thead th').each(function (idx) {
            var txt = $.trim($(this).text()).toLowerCase();
            if (txt === 'actions' || txt === 'action' || txt === 'preview' || txt === '') {
              nonOrderable.push(idx);
            }
          });

          $tbl.DataTable({
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [],
            columnDefs: nonOrderable.length ? [{ orderable: false, targets: nonOrderable }] : [],
            language: {
              emptyTable: emptyMsg,
              search: '',
              searchPlaceholder: 'Search records...',
              lengthMenu: 'Show _MENU_ entries'
            }
            });
          });
        })();

        // Split-layout Infinite Scroll + Live Search initializer
        (function () {
          var tableBody = document.getElementById('infiniteTableBody');
          if (!tableBody || tableBody.dataset.infiniteInit) return;
          tableBody.dataset.infiniteInit = '1';

          var rows = Array.from(tableBody.querySelectorAll('.infinite-item-row'));
          var searchInput = document.getElementById('infiniteSearchInput');
          var clearBtn = document.getElementById('clearInfiniteSearch');
          var displayedCountEl = document.getElementById('displayedCount');
          var totalCountEl = document.getElementById('totalCount');
          var noResultsRow = document.getElementById('noFilterResultsRow');
          var sentinel = document.getElementById('infiniteScrollSentinel');
          var spinner = document.getElementById('loadingSpinner');
          var endOfList = document.getElementById('endOfList');
          var batchSize = 10;
          var visibleLimit = batchSize;
          var isLoading = false;

          function renderRows() {
            var q = (searchInput ? searchInput.value : '').trim().toLowerCase();
            if (clearBtn) clearBtn.classList.toggle('d-none', q === '');
            var matched = rows.filter(function (r) {
              return !q || (r.getAttribute('data-search') || '').indexOf(q) !== -1;
            });
            rows.forEach(function (r) { r.style.display = 'none'; });
            var toShow = matched.slice(0, visibleLimit);
            toShow.forEach(function (r) { r.style.display = ''; });
            if (displayedCountEl) displayedCountEl.textContent = toShow.length;
            if (totalCountEl) totalCountEl.textContent = matched.length;
            if (noResultsRow) noResultsRow.style.display = matched.length === 0 ? '' : 'none';
            if (endOfList) {
              endOfList.classList.toggle('d-none', matched.length === 0 || toShow.length < matched.length);
            }
            return { shown: toShow.length, total: matched.length };
          }

          function loadMore() {
            if (isLoading) return;
            var state = renderRows();
            if (state.shown >= state.total) return;
            isLoading = true;
            if (spinner) spinner.classList.remove('d-none');
            setTimeout(function () {
              visibleLimit += batchSize;
              if (spinner) spinner.classList.add('d-none');
              isLoading = false;
              renderRows();
            }, 180);
          }

          if (searchInput) {
            searchInput.addEventListener('input', function () {
              visibleLimit = batchSize;
              renderRows();
            });
          }
          if (clearBtn) {
            clearBtn.addEventListener('click', function () {
              searchInput.value = '';
              visibleLimit = batchSize;
              renderRows();
            });
          }
          if ('IntersectionObserver' in window && sentinel) {
            var obs = new IntersectionObserver(function (entries) {
              entries.forEach(function (entry) {
                if (entry.isIntersecting) loadMore();
              });
            }, { rootMargin: '150px' });
            obs.observe(sentinel);
          }
          renderRows();
        })();
      });
    </script>
  </body>
</html>
