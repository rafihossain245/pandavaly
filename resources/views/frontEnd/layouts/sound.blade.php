@php
    /**
     * Optional audio cues (Website Settings → Sounds).
     *
     * Browsers refuse to play sound until the visitor has interacted with the
     * page, so an unsolicited cue is blocked on a first visit. Rather than
     * pretend otherwise, this arms the cue and plays it on the first real
     * gesture — and gives the visitor a mute control, remembered per browser,
     * because sound they did not ask for is the fastest way to lose them.
     */
    $soundSrc = $sound ?? null;
    // Welcome cue: once per browser session. The order cue has its own page
    // and should play every time that page is reached.
    $soundOnce = $soundOnce ?? false;
    // Each cue needs its own element id and storage key: the receipt page
    // renders the layout's cue AND its own, and two elements sharing an id
    // meant getElementById() returned the wrong one.
    $soundKey = $soundKey ?? 'default';
@endphp

@if($soundSrc)
    <audio id="pvSound-{{ $soundKey }}" src="{{ asset($soundSrc) }}" preload="auto"></audio>

    <button type="button" id="pvSoundToggle-{{ $soundKey }}" class="pv-sound-toggle" hidden
            aria-label="Sound on or off" title="Sound">
        <i class="fas fa-volume-high"></i>
    </button>

    <style>
        .pv-sound-toggle {
            position: fixed; left: 16px; bottom: 16px; z-index: 70;
            width: 40px; height: 40px; border-radius: 50%; cursor: pointer;
            border: 1px solid rgba(0,0,0,.08); background: #fff; color: #6b7280;
            box-shadow: 0 2px 10px rgba(0,0,0,.12); font-size: 15px;
        }
        .pv-sound-toggle.is-muted { color: #c3c3cf; }
        .pv-sound-toggle:hover { color: #e6007e; }
        @media print { .pv-sound-toggle { display: none; } }
    </style>

    <script>
    (function () {
        var el = document.getElementById('pvSound-{{ $soundKey }}'),
            btn = document.getElementById('pvSoundToggle-{{ $soundKey }}'),
            KEY = 'pv-sound-muted',
            ONCE = {{ $soundOnce ? 'true' : 'false' }},
            SESSION_KEY = 'pv-sound-played-{{ $soundKey }}',
            played = false;

        if (!el) return;

        function muted() { try { return localStorage.getItem(KEY) === '1'; } catch (e) { return false; } }
        function setMuted(v) { try { localStorage.setItem(KEY, v ? '1' : '0'); } catch (e) {} }

        function paint() {
            btn.hidden = false;
            btn.classList.toggle('is-muted', muted());
            btn.querySelector('i').className = muted() ? 'fas fa-volume-xmark' : 'fas fa-volume-high';
        }

        function sessionPlayed() {
            try { return sessionStorage.getItem(SESSION_KEY) === '1'; } catch (e) { return false; }
        }

        function markPlayed() {
            played = true;
            if (!ONCE) return;
            try { sessionStorage.setItem(SESSION_KEY, '1'); } catch (e) {}
        }

        function play() {
            if (played || muted()) return;
            if (ONCE && sessionPlayed()) return;

            var p = el.play();

            // Mark as played only once playback actually starts. Marking up
            // front looked harmless but meant the blocked first attempt burned
            // the session flag, so the retry on the visitor's first gesture
            // returned early and the cue never played at all.
            if (p && p.then) {
                p.then(markPlayed).catch(function () { /* still awaiting a gesture */ });
            } else {
                markPlayed(); // older browsers return undefined
            }
        }

        // Try immediately — succeeds where the visitor already has a history
        // with this site — then fall back to the first genuine gesture.
        play();

        ['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach(function (evt) {
            window.addEventListener(evt, function once() {
                play();
                ['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach(function (e2) {
                    window.removeEventListener(e2, once);
                });
            }, { once: true, passive: true });
        });

        btn.addEventListener('click', function () {
            var next = !muted();
            setMuted(next);
            if (next) { el.pause(); el.currentTime = 0; } else { played = false; play(); }
            paint();
        });

        paint();
    })();
    </script>
@endif
