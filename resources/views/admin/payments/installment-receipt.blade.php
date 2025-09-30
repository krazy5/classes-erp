<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installment Receipt - {{ $payment->student->name ?? 'student' }}</title>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; margin: 0; padding: 32px; background: #f8fafc; color: #0f172a; }
        .wrapper { max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 16px; box-shadow: 0 20px 45px -20px rgba(15,23,42,.24); padding: 32px; }
        h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .meta { margin-top: 24px; display: grid; gap: 18px; font-size: 13px; }
        .meta div span { display: block; color: #64748b; text-transform: uppercase; letter-spacing: .08em; font-weight: 600; margin-bottom: 4px; }
        .section { margin-top: 28px; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; }
        .section h2 { font-size: 15px; font-weight: 700; margin-bottom: 12px; }
        .grid { display: grid; gap: 12px; font-size: 13px; }
        .grid.two { grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); }
        .label { color: #475569; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; font-size: 11px; }
        .value { color: #0f172a; font-weight: 600; margin-top: 4px; }
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; }
        .badge.settled { background: #dcfce7; color: #166534; }
        .badge.overdue { background: #fee2e2; color: #b91c1c; }
        .badge.partial { background: #e0e7ff; color: #3730a3; }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .actions { margin-top: 28px; display: flex; gap: 16px; justify-content: center; }
        .button { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; border: 1px solid #cbd5f5; background: #fff; font-size: 13px; font-weight: 600; color: #1d4ed8; text-decoration: none; }
        .note { margin-top: 20px; font-size: 12px; color: #64748b; }
        @media print {
            body { background: #ffffff; padding: 0; }
            .wrapper { box-shadow: none; border: 1px solid #cbd5f5; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>{{ config('app.name') }} — Installment Receipt</h1>
        <p style="margin-top:6px; font-size:13px; color:#64748b;">Installment {{ $installment->sequence }} acknowledgement for {{ $payment->student->name ?? 'student' }}.</p>

        <div class="meta">
            <div>
                <span>Receipt number</span>
                <strong>{{ $installment->receipt_number ?? ('TEMP-' . str_pad($installment->id, 6, '0', STR_PAD_LEFT)) }}</strong>
            </div>
            <div>
                <span>Generated</span>
                {{ $generatedAt->format('d M Y H:i') }}
            </div>
        </div>

        <div class="section">
            <h2>Payment summary</h2>
            <div class="grid two">
                <div>
                    <div class="label">Amount due</div>
                    <div class="value">{{ number_format($installment->amount, 2) }}</div>
                </div>
                <div>
                    <div class="label">Amount received</div>
                    <div class="value">{{ number_format($installment->paid_amount, 2) }}</div>
                </div>
                <div>
                    <div class="label">Due date</div>
                    <div class="value">{{ $installment->due_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="label">Payment date</div>
                    <div class="value">{{ $installment->paid_at?->format('d M Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="label">Method</div>
                    <div class="value">{{ $installment->payment_method ? ucfirst(str_replace('_',' ', $installment->payment_method)) : '—' }}</div>
                </div>
                <div>
                    <div class="label">Reference</div>
                    <div class="value">{{ $installment->reference ?: '—' }}</div>
                </div>
                <div>
                    <div class="label">Status</div>
                    <div class="value">
                        @php
                            $badge = match($installment->status) {
                                'settled' => 'settled',
                                'overdue' => 'overdue',
                                'partial' => 'partial',
                                default => 'pending',
                            };
                        @endphp
                        <span class="badge {{ $badge }}">{{ ucfirst($installment->status) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Student details</h2>
            <div class="grid two">
                <div>
                    <div class="label">Student</div>
                    <div class="value">{{ $payment->student->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="label">Class group</div>
                    <div class="value">{{ $payment->student->classGroup->name ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Payment plan</h2>
            <div class="grid two">
                <div>
                    <div class="label">Plan reference</div>
                    <div class="value">Plan #{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div>
                    <div class="label">Fee structure</div>
                    <div class="value">{{ $payment->feeStructure->name ?? 'Custom plan' }}</div>
                </div>
                <div>
                    <div class="label">Total due</div>
                    <div class="value">{{ number_format($payment->total_amount, 2) }}</div>
                </div>
                <div>
                    <div class="label">Outstanding balance</div>
                    <div class="value">{{ number_format($payment->outstanding_amount, 2) }}</div>
                </div>
            </div>
        </div>

        @if($installment->remarks)
            <div class="section">
                <h2>Remarks</h2>
                <p style="font-size:13px; color:#475569; margin:0;">{{ $installment->remarks }}</p>
            </div>
        @endif

        <div class="note">This installment receipt is generated from {{ config('app.name') }}. Please retain it for your records.</div>

        @if(!request()->boolean('download'))
            <div class="actions">
                <a href="{{ route('admin.payments.installments.receipt', [$payment, $installment, 'download' => true]) }}" class="button">Download copy</a>
                <button type="button" class="button" onclick="window.print()">Print</button>
            </div>
        @endif
    </div>
</body>
</html>
