@extends('user.layout')
@section('title', 'Create Ticket - SMM Panel')
@section('user_content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.tickets.index') }}">Tickets</a></li>
                <li class="breadcrumb-item active" aria-current="page">New Ticket</li>
            </ol>
        </nav>
        <h2 class="mb-0">
            <i class="fas fa-plus-circle me-2 text-primary"></i>Create New Ticket
        </h2>
    </div>
    <a href="{{ route('user.tickets.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-ticket-alt me-2 text-primary"></i>Submit a Support Request
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.tickets.store') }}">
                    @csrf

                    {{-- Subject --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Subject <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="subject"
                               class="form-control form-control-lg @error('subject') is-invalid @enderror"
                               value="{{ old('subject') }}"
                               placeholder="Brief summary of your issue"
                               required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Priority --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Priority <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="form-check custom-form-check">
                                    <input type="radio"
                                           name="priority"
                                           id="priority_low"
                                           value="low"
                                           class="form-check-input"
                                           {{ old('priority') == 'low' || !old('priority') ? 'checked' : '' }}>
                                    <label class="form-check-label w-100 p-3 border rounded text-center cursor-pointer"
                                           for="priority_low">
                                        <i class="fas fa-minus-circle text-secondary fa-lg mb-2 d-block"></i>
                                        <span class="fw-semibold">Low</span>
                                        <small class="d-block text-muted">General questions</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check custom-form-check">
                                    <input type="radio"
                                           name="priority"
                                           id="priority_medium"
                                           value="medium"
                                           class="form-check-input"
                                           {{ old('priority') == 'medium' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100 p-3 border rounded text-center cursor-pointer"
                                           for="priority_medium">
                                        <i class="fas fa-exclamation-circle text-warning fa-lg mb-2 d-block"></i>
                                        <span class="fw-semibold">Medium</span>
                                        <small class="d-block text-muted">Needs attention</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check custom-form-check">
                                    <input type="radio"
                                           name="priority"
                                           id="priority_high"
                                           value="high"
                                           class="form-check-input"
                                           {{ old('priority') == 'high' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100 p-3 border rounded text-center cursor-pointer"
                                           for="priority_high">
                                        <i class="fas fa-exclamation-triangle text-danger fa-lg mb-2 d-block"></i>
                                        <span class="fw-semibold">High</span>
                                        <small class="d-block text-muted">Urgent issue</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('priority')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Order Reference (Optional) --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Related Order <span class="text-muted">(Optional)</span>
                        </label>
                        <select name="order_id" class="form-select">
                            <option value="">Select an order (if applicable)</option>
                            @foreach($recentOrders ?? [] as $order)
                                <option value="{{ $order->id }}" {{ old('order_id') == $order->id ? 'selected' : '' }}>
                                    #{{ $order->order_id }} - {{ $order->service->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Link this ticket to a specific order</small>
                    </div>

                    {{-- Message --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Message <span class="text-danger">*</span>
                        </label>
                        <textarea name="message"
                                  class="form-control @error('message') is-invalid @enderror"
                                  rows="8"
                                  placeholder="Describe your issue in detail. Include any relevant order IDs, screenshots, or error messages."
                                  required>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">Be as detailed as possible for faster resolution</small>
                            <small class="text-muted"><span id="charCount">0</span> characters</small>
                        </div>
                    </div>

                    {{-- Attachments --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Attachments <span class="text-muted">(Optional)</span>
                        </label>
                        <input type="file"
                               name="attachments[]"
                               class="form-control @error('attachments.*') is-invalid @enderror"
                               multiple
                               accept="image/*,.pdf,.doc,.docx">
                        @error('attachments.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Max 5 files. Allowed types: JPG, PNG, PDF, DOC. Max size: 5MB each.
                        </small>
                    </div>

                    {{-- Submit --}}
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- FAQ Section --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-question-circle me-2 text-info"></i>Before Creating a Ticket
                </h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                My order is not completing. What should I do?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                First, check your order status on the Orders page. If the order shows "In Progress" but is taking longer than expected,
                                please wait for the average time mentioned. If it exceeds that time, you can request a refill or cancel the order.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                How long does it take to get a response?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                We typically respond within 24 hours. High priority tickets get faster responses.
                                You will receive an email notification when we reply to your ticket.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                My deposit was not credited. What to do?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                For manual payment methods, deposits are verified manually and may take up to 24 hours.
                                Please keep your transaction receipt/ID ready. For instant deposits, try refreshing the page or contact support.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Character count for message
    const messageTextarea = document.querySelector('textarea[name="message"]');
    const charCount = document.getElementById('charCount');

    if (messageTextarea && charCount) {
        messageTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Highlight selected priority
    document.querySelectorAll('.custom-form-check input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.custom-form-check label').forEach(label => {
                label.classList.remove('border-primary', 'bg-primary', 'bg-opacity10');
            });
            if (this.checked) {
                this.nextElementSibling.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .custom-form-check input[type="radio"] {
        display: none;
    }
    .custom-form-check label {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .custom-form-check input[type="radio"]:checked + label {
        border-color: #667eea !important;
        background: rgba(102, 126, 234, 0.1);
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endpush
