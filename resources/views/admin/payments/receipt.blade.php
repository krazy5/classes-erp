<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt - {{ $payment->student->name ?? 'student' }}</title>
    <style>
        :root { color-scheme: light dark; }
        body { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; padding: 32px; background: #f8fafc; color: #0f172a; }
        h1, h2, h3 { margin: 0; }
        .receipt { max-width: 800px; margin: 0 auto; background: #ffffff; border-radius: 16px; box-shadow: 0 20px 45px -20px rgba(15, 23, 42, 0.3); padding: 32px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
        .meta { margin-top: 24px; display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap: 16px; font-size: 13px; }
        .meta span { display: block; color: #64748b; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; font-size: 13px; }
        th { text-align: left; background: #f1f5f9; text-transform: uppercase; letter-spacing: 0.08em; font-size: 12px; color: #475569; }
        th, td { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; }
        tfoot td { font-weight: 600; font-size: 14px; }
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; }
        .badge.paid { background: #dcfce7; color: #166534; }
        .badge.partial { background: #e0e7ff; color: #3730a3; }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .badge.overdue { background: #fee2e2; color: #b91c1c; }
        .footer { margin-top: 32px; font-size: 12px; color: #64748b; }
        .actions { margin: 24px auto 0; display: flex; gap: 16px; justify-content: center; }
        .button { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; border: 1px solid #cbd5f5; background: #fff; font-size: 13px; font-weight: 600; color: #1d4ed8; text-decoration: none; }
        @media print {
            body { background: #ffffff; padding: 0; }
            .receipt { box-shadow: none; border: 1px solid #cbd5f5; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    @php
        $settings = $tenantSettings ?? [];
        $institutionName = $institutionName ?? ($settings['institute_name'] ?? config('app.name'));
        $logoUrl = $settings['institute_logo'] ?? null;
    @endphp

    <div class="receipt">
        <div class="header">
            <div style="display: flex; gap: 16px; align-items: center;">
                @if($logoUrl)
                    <span style="display: inline-flex; height: 56px; width: 56px; align-items: center; justify-content: center; border-radius: 16px; background: #f1f5f9; overflow: hidden;">
                        <img src="{{ $logoUrl }}" alt="{{ $institutionName }} logo" style="max-height: 48px; max-width: 48px; object-fit: contain;">
                    </span>
                @endif
                <div>
                    <h1 style="font-size: 24px; font-weight: 700;">{{ $institutionName }} Fee Receipt</h1>
                    <p style="margin-top: 6px; font-size: 13px; color: #64748b;">Official acknowledgement for payments received.</p>
                </div>
            </div>
            <div style="text-align: right; font-size: 13px; color: #475569;">
                <div>Receipt #: {{ 'RC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div>Generated: {{ $generatedAt->format('d M Y H:i') }}</div>
            </div>
        </div>

        <div class="meta">
            <div>
                <span>Student</span>
                <strong>{{ $payment->student->name ?? 'N/A' }}</strong><br>
                {{ $payment->student->classGroup->name ?? 'Class not assigned' }}
            </div>
            <div>
                <span>Plan Reference</span>
                Plan #{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}<br>
                {{ $payment->feeStructure->name ?? 'Custom payment plan' }}
            </div>
            <div>
                <span>Summary</span>
                Total due: {{ number_format($payment->total_amount, 2) }}<br>
                Collected: {{ number_format($payment->amount_paid, 2) }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                    <th>Paid Amount</th>
                    <th>Paid On</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                @foreach($installments as $installment)
                    <tr>
                        <td>{{ $installment->sequence }}</td>
                        <td>{{ $installment->due_date?->format('d M Y') ?? 'N/A' }}</td>
                        <td>{{ number_format($installment->amount, 2) }}</td>
                        <td>{{ number_format($installment->paid_amount, 2) }}</td>
                        <td>{{ $installment->paid_at?->format('d M Y') ?? 'N/A' }}</td>
                        <td>{{ $installment->payment_method ? ucfirst(str_replace('_',' ', $installment->payment_method)) : 'N/A' }}</td>
                        @php
                            $badge = match($installment->status) {
                                'settled' => 'paid',
                                'partial' => 'partial',
                                'overdue' => 'overdue',
                                default => 'pending',
                            };
                        @endphp
                        <td><span class="badge {{ $badge }}">{{ ucfirst($installment->status) }}</span></td>
                        <td>{{ $installment->reference ?: 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Totals</td>
                    <td>{{ number_format($installments->sum('amount'), 2) }}</td>
                    <td>{{ number_format($installments->sum('paid_amount'), 2) }}</td>
                    <td colspan="4" style="text-align: right;">Outstanding balance: {{ number_format($payment->outstanding_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if($payment->notes)
            <div style="margin-top: 24px; font-size: 13px;">
                <strong>Notes:</strong>
                <p style="margin-top: 6px; color: #475569;">{{ $payment->notes }}</p>
            </div>
        @endif

        <div class="footer">
            This receipt is auto-generated from the {{ $institutionName }} system. For assistance, contact the administration office.
        </div>

        @if(!request()->boolean('download'))
            <div class="actions">
                <a href="{{ route('admin.payments.receipt', [$payment, 'download' => true]) }}" class="button">Download copy</a>
                <button type="button" class="button" onclick="window.print()">Print</button>
            </div>
        @endif
    </div>
</body>
</html>
