<div class="tab-pane" id="tab-badges" role="tabpanel">
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                Badge types: {{ $badge_type_count }}<br/>
                Badges: {{ $badge_count }}<br/>
                Unseen badges: {{ $unseen_badge_count }}<br/>
                <form method="POST" action="/admin/badges/refresh" class="inline-block">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-primary">Refresh badges</button>
                </form>
                @if (!empty($badge_refresh_run))
                    <hr/>
                    <strong>Refresh status:</strong>
                    <div id="badge-refresh-status" data-run-id="{{ $badge_refresh_run }}">Starting...</div>
                @endif
            </div>
        </div>
    </div>
</div>

@if (!empty($badge_refresh_run))
    <script type="text/javascript">
        (function() {
            var statusEl = document.getElementById('badge-refresh-status');
            if (!statusEl) {
                return;
            }

            var runId = statusEl.getAttribute('data-run-id');
            var statusUrl = '/admin/badges/refresh/status/' + encodeURIComponent(runId);

            function renderStatus(data) {
                if (!data || !data.status) {
                    statusEl.textContent = 'Status unavailable.';
                    return false;
                }

                if (data.status === 'queued') {
                    statusEl.textContent = 'Queued...';
                    return true;
                }

                if (data.status === 'running') {
                    var total = data.users_total || '?';
                    var processed = data.users_processed || 0;
                    statusEl.textContent = 'Running... users processed: ' + processed + '/' + total;
                    return true;
                }

                if (data.status === 'done') {
                    statusEl.textContent = 'Done. Badges added: ' + data.badges_added + '; time taken: ' + data.duration_seconds + 's';
                    return false;
                }

                if (data.status === 'failed') {
                    statusEl.textContent = 'Failed: ' + (data.error || 'unknown error');
                    return false;
                }

                statusEl.textContent = 'Status: ' + data.status;
                return false;
            }

            function poll() {
                $.getJSON(statusUrl)
                    .done(function(data) {
                        if (renderStatus(data)) {
                            setTimeout(poll, 3000);
                        }
                    })
                    .fail(function() {
                        statusEl.textContent = 'Status lookup failed.';
                    });
            }

            poll();
        })();
    </script>
@endif
