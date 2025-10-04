<div class="relative">
    <button type="button" wire:click="open" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
        Mark My Attendance
    </button>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/70" aria-hidden="true"></div>
            <div class="relative z-10 w-full max-w-3xl rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Scan or Upload QR Code</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Use the camera or upload an image to mark your attendance for today.</p>
                    </div>
                    <button type="button" wire:click="close" class="rounded-full bg-slate-100 p-2 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                        <span class="sr-only">Close</span>
                        &times;
                    </button>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,340px),1fr]">
                    <div class="space-y-4">
                        <div id="{{ $elementId }}-preview" class="flex h-60 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                            Allow camera access or upload a QR image to continue.
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button type="button" id="{{ $elementId }}-start" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                Start Camera
                            </button>
                            <button type="button" id="{{ $elementId }}-stop" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                                Stop Camera
                            </button>
                            <button type="button" id="{{ $elementId }}-restart" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                                Restart Scanner
                            </button>
                            <button type="button" id="{{ $elementId }}-upload" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                                Upload QR Image
                            </button>
                        </div>
                        <input id="{{ $elementId }}-file" type="file" accept="image/png,image/jpeg,image/jpg" class="hidden" />
                        <span id="{{ $elementId }}-status" class="block min-h-[1.5rem] text-xs text-slate-500 dark:text-slate-400"></span>
                    </div>

                    <div class="space-y-4 text-sm">
                        @if ($statusMessage)
                            <div @class([
                                'rounded-lg px-4 py-3',
                                'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-600' => $statusLevel === 'success',
                                'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-600' => $statusLevel === 'info',
                                'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-900/30 dark:text-rose-200 dark:border-rose-600' => $statusLevel === 'error',
                            ])>
                                {{ $statusMessage }}
                            </div>
                        @endif

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/40">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Tips</h3>
                            <ul class="mt-2 space-y-2 text-xs text-slate-600 dark:text-slate-400">
                                <li>&bull; Allow camera access when prompted; use the back camera on phones for best focus.</li>
                                <li>&bull; Hold the code steady in the frame and ensure it is well lit.</li>
                                <li>&bull; Upload a sharp PNG/JPG image if scanning from another screen.</li>
                            </ul>
                        </div>

                        <button type="button" wire:click="close" class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script src="{{ asset('vendor/html5-qrcode.min.js') }}" defer></script>
        <script>
            document.addEventListener('livewire:init', () => {
                let activeId = null;
                let cameraInstance = null;
                let lastDecodedText = null;

                const ensureLibraryLoaded = () => new Promise((resolve, reject) => {
                    if (window.Html5Qrcode) {
                        resolve();
                        return;
                    }

                    const existing = document.querySelector('script[data-qr-lib]');
                    if (existing) {
                        existing.addEventListener('load', () => resolve());
                        existing.addEventListener('error', () => reject(new Error('Failed to load QR library')));
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = "{{ asset('vendor/html5-qrcode.min.js') }}";
                    script.async = true;
                    script.setAttribute('data-qr-lib', 'true');
                    script.addEventListener('load', () => resolve());
                    script.addEventListener('error', () => reject(new Error('Failed to load QR library')));
                    document.head.appendChild(script);
                });

                const previewEl = () => activeId ? document.getElementById(`${activeId}-preview`) : null;
                const statusEl = () => activeId ? document.getElementById(`${activeId}-status`) : null;
                const fileInputEl = () => activeId ? document.getElementById(`${activeId}-file`) : null;

                const setPreviewPlaceholder = (message) => {
                    const preview = previewEl();
                    if (preview) {
                        preview.innerHTML = `<div class="flex h-60 items-center justify-center text-center text-sm text-slate-500 dark:text-slate-400">${message}</div>`;
                    }
                };

                const setStatus = (message) => {
                    const el = statusEl();
                    if (el) {
                        el.textContent = message || '';
                    }
                };

                const disposeScanner = async (statusMessage = '') => {
                    if (cameraInstance) {
                        try {
                            await cameraInstance.stop();
                        } catch (error) {
                            // ignore stop errors
                        }
                        try {
                            await cameraInstance.clear();
                        } catch (error) {
                            // ignore clear errors
                        }
                        cameraInstance = null;
                    }
                    setPreviewPlaceholder('Camera idle. Start scanning or upload a QR image.');
                    if (statusMessage) {
                        setStatus(statusMessage);
                    }
                };

                const handleDecodedText = (decodedText) => {
                    if (!decodedText || decodedText === lastDecodedText) {
                        return;
                    }

                    lastDecodedText = decodedText;
                    setStatus('QR code detected. Validating...');
                    Livewire.dispatch('student-attendance:token', { token: decodedText });
                };

                const startCamera = async () => {
                    try {
                        await ensureLibraryLoaded();
                    } catch (error) {
                        console.error('QR library failed to load', error);
                        setStatus('Unable to load the QR scanner. Refresh the page and try again.');
                        return;
                    }

                    if (!activeId) {
                        return;
                    }

                    await disposeScanner('');
                    setStatus('Requesting camera access...');

                    try {
                        const devices = await Html5Qrcode.getCameras();
                        if (!devices.length) {
                            setPreviewPlaceholder('No camera available. Use upload instead.');
                            setStatus('No camera devices found.');
                            return;
                        }

                        const preferredDevice = devices.find(({ label }) => /back|rear|environment/i.test(label)) ?? devices[0];
                        cameraInstance = new Html5Qrcode(`${activeId}-preview`, { verbose: false });

                        await cameraInstance.start(
                            { deviceId: { exact: preferredDevice.id } },
                            { fps: 10, qrbox: { width: 260, height: 260 } },
                            (decodedText) => handleDecodedText(decodedText),
                            () => {
                                // keep silent on continuous scan failures to avoid spamming status updates
                            }
                        );

                        setStatus('Camera active. Hold the QR code inside the frame.');
                    } catch (error) {
                        console.error('Unable to start camera', error);
                        setStatus('Unable to access the camera. Check permissions or try uploading an image.');
                        await disposeScanner('');
                    }
                };

                const stopCamera = async () => {
                    await disposeScanner('Scanner stopped.');
                };

                const restartCamera = async () => {
                    lastDecodedText = null;
                    setStatus('Restarting scanner...');
                    await disposeScanner('');
                    await startCamera();
                };

                const bindControls = (id) => {
                    const startBtn = document.getElementById(`${id}-start`);
                    if (startBtn && startBtn.dataset.bound !== 'true') {
                        startBtn.dataset.bound = 'true';
                        startBtn.addEventListener('click', () => {
                            lastDecodedText = null;
                            startCamera();
                        });
                    }

                    const stopBtn = document.getElementById(`${id}-stop`);
                    if (stopBtn && stopBtn.dataset.bound !== 'true') {
                        stopBtn.dataset.bound = 'true';
                        stopBtn.addEventListener('click', () => {
                            stopCamera();
                        });
                    }

                    const restartBtn = document.getElementById(`${id}-restart`);
                    if (restartBtn && restartBtn.dataset.bound !== 'true') {
                        restartBtn.dataset.bound = 'true';
                        restartBtn.addEventListener('click', () => {
                            restartCamera();
                        });
                    }

                    const uploadBtn = document.getElementById(`${id}-upload`);
                    const fileInput = fileInputEl();

                    if (uploadBtn && uploadBtn.dataset.bound !== 'true' && fileInput) {
                        uploadBtn.dataset.bound = 'true';
                        uploadBtn.addEventListener('click', () => fileInput.click());
                    }

                    if (fileInput && fileInput.dataset.bound !== 'true') {
                        fileInput.dataset.bound = 'true';
                        fileInput.addEventListener('change', async (event) => {
                            const file = event.target.files?.[0];
                            if (!file) {
                                return;
                            }

                            try {
                                await ensureLibraryLoaded();
                            } catch (error) {
                                console.error('QR library failed to load', error);
                                setStatus('Unable to load the QR scanner. Refresh the page and try again.');
                                event.target.value = '';
                                return;
                            }
                            lastDecodedText = null;
                            setStatus('Processing QR code image...');
                            await disposeScanner('');

                            try {
                                cameraInstance = new Html5Qrcode(`${activeId}-preview`, { verbose: false });
                                const decodedText = await cameraInstance.scanFile(file, true);
                                setStatus('QR code detected. Validating...');
                                handleDecodedText(decodedText);
                            } catch (error) {
                                console.error('Unable to read QR image', error);
                                setStatus('Unable to read that QR code image. Try another image or use the camera.');
                                Livewire.dispatch('qr-scan-error');
                            } finally {
                                if (cameraInstance) {
                                    try {
                                        await cameraInstance.clear();
                                    } catch (clearError) {
                                        // ignore clear errors
                                    }
                                    cameraInstance = null;
                                }
                                setPreviewPlaceholder('Camera idle. Start scanning or upload a QR image.');
                                fileInput.value = '';
                            }
                        });
                    }
                };

                const ensureControlsBound = (id, attempt = 0) => {
                    if (!id) {
                        return;
                    }

                    const startBtn = document.getElementById(`${id}-start`);
                    const uploadBtn = document.getElementById(`${id}-upload`);

                    if (startBtn || uploadBtn) {
                        bindControls(id);
                        return;
                    }

                    if (attempt > 25) {
                        console.warn('QR modal controls missing after repeated attempts.', { id });
                        return;
                    }

                    setTimeout(() => ensureControlsBound(id, attempt + 1), 50);
                };

                Livewire.hook('message.processed', () => {
                    if (activeId) {
                        ensureControlsBound(activeId);
                    }
                });

                Livewire.on('qr-modal-opened', ({ id }) => {
                    activeId = id;
                    lastDecodedText = null;
                    setPreviewPlaceholder('Camera idle. Start scanning or upload a QR image.');
                    setStatus('');
                    ensureControlsBound(id);
                });

                Livewire.on('qr-modal-closed', async () => {
                    await disposeScanner('');
                    activeId = null;
                    lastDecodedText = null;
                });

                Livewire.on('qr-scan-success', async () => {
                    await disposeScanner('Attendance marked successfully!');
                });

                Livewire.on('qr-scan-error', () => {
                    lastDecodedText = null;
                    setStatus('Invalid or expired QR code. Try again.');
                });

                Livewire.on('attendance-updated', () => {
                    setTimeout(() => window.location.reload(), 1200);
                });
            });
        </script>
    @endpush
@endonce




