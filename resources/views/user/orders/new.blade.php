@extends('user.layout')
@section('title', 'New Order - SMM Panel')
@section('user_content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-plus-circle me-2 text-primary"></i>New Order
        </h2>
        <p class="text-muted mb-0">Place a new order for social media services</p>
    </div>
</div>

{{-- Order Form --}}
<div x-data="orderForm()" x-init="init()">
    <form @submit.prevent="submitOrder">
        <div class="row g-4">
            {{-- Left Column - Category & Service --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>Select Service
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Category Selection --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-folder-open me-1"></i>Category
                            </label>
                            <select x-model="selectedCategory"
                                    @change="loadServices()"
                                    class="form-select form-select-lg"
                                    :class="{ 'is-invalid': errors.category_id }">
                                <option value="">Select a category</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" x-show="errors.category_id" x-text="errors.category_id"></div>
                        </div>

                        {{-- Service Selection --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-bolt me-1"></i>Service
                            </label>
                            <select x-model="selectedService"
                                    @change="loadServiceDetails()"
                                    class="form-select form-select-lg"
                                    :class="{ 'is-invalid': errors.service_id }"
                                    :disabled="!selectedCategory || loading.services">
                                <option value="">Select a service</option>
                                <template x-for="service in services" :key="service.id">
                                    <option :value="service.id" x-text="service.name + ' - $' + service.price.toFixed(4) + '/1K'"></option>
                                </template>
                            </select>
                            <div class="invalid-feedback" x-show="errors.service_id" x-text="errors.service_id"></div>
                            <div x-show="loading.services" class="mt-2 text-muted small">
                                <i class="fas fa-spinner fa-spin me-1"></i>Loading services...
                            </div>
                        </div>

                        {{-- Service Details --}}
                        <template x-if="selectedServiceData">
                            <div class="alert alert-info border-0 mb-0" style="background: #e7f5ff;">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Min Order</small>
                                        <span class="fw-semibold" x-text="selectedServiceData.min + ' ' + (selectedServiceData.max).toLocaleString()"></span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Max Order</small>
                                        <span class="fw-semibold" x-text="selectedServiceData.max.toLocaleString()"></span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Price per 1K</small>
                                        <span class="fw-semibold text-success" x-text="'$' + selectedServiceData.price.toFixed(4)"></span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Average Time</small>
                                        <span class="fw-semibold" x-text="selectedServiceData.avg_time || 'N/A'"></span>
                                    </div>
                                </div>
                                <template x-if="selectedServiceData.description">
                                    <p class="mt-3 mb-0 small" x-text="selectedServiceData.description"></p>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Right Column - Order Details --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart me-2 text-primary"></i>Order Details
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Link Input --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-link me-1"></i>Link
                                <span class="text-danger">*</span>
                            </label>
                            <input type="url"
                                   x-model="link"
                                   @input="errors.link = ''"
                                   class="form-control form-control-lg"
                                   :class="{ 'is-invalid': errors.link }"
                                   placeholder="https://instagram.com/p/ABC123/ or @username">
                            <div class="invalid-feedback" x-show="errors.link" x-text="errors.link"></div>
                            <small class="text-muted">Enter the post URL or username</small>
                        </div>

                        {{-- Quantity Input --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-hashtag me-1"></i>Quantity
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   x-model.number="quantity"
                                   @input="calculateTotal(); errors.quantity = ''"
                                   class="form-control form-control-lg"
                                   :class="{ 'is-invalid': errors.quantity }"
                                   :min="selectedServiceData?.min || 1"
                                   :max="selectedServiceData?.max || 999999"
                                   placeholder="Enter quantity">
                            <div class="invalid-feedback" x-show="errors.quantity" x-text="errors.quantity"></div>
                            <template x-if="selectedServiceData">
                                <small class="text-muted">
                                    Min: <span x-text="selectedServiceData.min"></span> |
                                    Max: <span x-text="selectedServiceData.max.toLocaleString()"></span>
                                </small>
                            </template>
                        </div>

                        {{-- Dripfeed Option --}}
                        @if(isset($features['dripfeed']) && $features['dripfeed'])
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="checkbox"
                                       x-model="dripfeed.enabled"
                                       @change="dripfeed.enabled ? dripfeedPanel = true : dripfeedPanel = false"
                                       class="form-check-input"
                                       id="dripfeedCheck">
                                <label class="form-check-label fw-semibold" for="dripfeedCheck">
                                    <i class="fas fa-clock me-1"></i>Enable Dripfeed
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">Orders are spread evenly over time instead of being sent all at once</small>

                            <template x-if="dripfeedPanel">
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Runs</label>
                                        <input type="number"
                                               x-model.number="dripfeed.runs"
                                               class="form-control"
                                               min="1"
                                               placeholder="e.g. 10">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Interval (minutes)</label>
                                        <input type="number"
                                               x-model.number="dripfeed.interval"
                                               class="form-control"
                                               min="1"
                                               placeholder="e.g. 30">
                                    </div>
                                </div>
                            </template>
                        </div>
                        @endif

                        {{-- Subscription Option --}}
                        @if(isset($features['subscription']) && $features['subscription'])
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="checkbox"
                                       x-model="subscription.enabled"
                                       class="form-check-input"
                                       id="subscriptionCheck">
                                <label class="form-check-label fw-semibold" for="subscriptionCheck">
                                    <i class="fas fa-sync me-1"></i>Enable Subscription
                                </label>
                            </div>

                            <template x-if="subscription.enabled">
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Expiry Date</label>
                                        <input type="date"
                                               x-model="subscription.expiry"
                                               class="form-control">
                                    </div>
                                </div>
                            </template>
                        </div>
                        @endif

                        {{-- Custom Comments --}}
                        <template x-if="selectedServiceData?.custom_comments">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-comments me-1"></i>Custom Comments
                                </label>
                                <textarea x-model="custom_comments"
                                          class="form-control"
                                          rows="5"
                                          placeholder="Enter one comment per line"></textarea>
                                <small class="text-muted">One comment per line</small>
                            </div>
                        </template>

                        {{-- Order Summary --}}
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    <i class="fas fa-receipt me-2 text-primary"></i>Order Summary
                                </h5>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <span class="text-muted">Service:</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="fw-semibold" x-text="selectedServiceData?.name || 'N/A'"></span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted">Quantity:</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="fw-semibold" x-text="quantity.toLocaleString() || '0'"></span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted">Price per 1K:</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="fw-semibold" x-text="'$' + (selectedServiceData?.price || 0).toFixed(4)"></span>
                                    </div>
                                    <template x-if="dripfeed.enabled">
                                        <template>
                                            <div class="col-6">
                                                <span class="text-muted">Dripfeed Runs:</span>
                                            </div>
                                            <div class="col-6 text-end">
                                                <span class="fw-semibold" x-text="dripfeed.runs + 'x'"></span>
                                            </div>
                                        </template>
                                    </template>
                                    <hr class="my-2">
                                    <div class="col-6">
                                        <span class="fw-bold">Total Charge:</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="fw-bold text-success h4 mb-0" x-text="'$' + total.toFixed(4)"></span>
                                    </div>
                                </div>
                                <div x-show="selectedServiceData && quantity > 0" class="mt-3">
                                    <small class="text-muted">
                                        You will receive approximately <span class="fw-semibold" x-text="quantity.toLocaleString()"></span>
                                        <span x-text="selectedServiceData?.name?.toLowerCase().includes('follower') ? 'followers' : 'items'"></span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Balance Check --}}
                        <template x-if="total > 0">
                            <div class="alert mb-4"
                                 :class="total > {{ auth()->user()->balance ?? 0 }} ? 'alert-danger' : 'alert-success'">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-wallet me-2"></i>
                                        Your Balance:
                                    </div>
                                    <span class="fw-bold h5 mb-0">
                                        ${{ number_format(auth()->user()->balance ?? 0, 2) }}
                                    </span>
                                </div>
                                <template x-if="total > {{ auth()->user()->balance ?? 0 }}">
                                    <div class="mt-2 mb-0">
                                        <small>Insufficient balance.
                                            <a href="{{ route('user.deposit') }}" class="alert-link">Add funds</a>
                                        </small>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Submit Button --}}
                        <button type="submit"
                                class="btn btn-primary btn-lg w-100"
                                :disabled="submitting || (total > {{ auth()->user()->balance ?? 0 }})">
                            <template x-if="submitting">
                                <span><i class="fas fa-spinner fa-spin me-2"></i>Processing...</span>
                            </template>
                            <template x-if="!submitting">
                                <span><i class="fas fa-paper-plane me-2"></i>Place Order - $<span x-text="total.toFixed(4)"></span></span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Success Modal --}}
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-5">
                <div class="text-success mb-4">
                    <i class="fas fa-check-circle fa-5x"></i>
                </div>
                <h3 class="mb-3">Order Placed Successfully!</h3>
                <p class="text-muted mb-4">Your order has been submitted and is being processed.</p>
                <div class="mb-4">
                    <p class="mb-1 text-muted">Order ID</p>
                    <h4><code id="newOrderId"></code></h4>
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="#" id="viewOrderBtn" class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i>View Order
                    </a>
                    <a href="{{ route('user.orders.new') }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i>New Order
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function orderForm() {
        return {
            selectedCategory: '{{ request('category') ?? '' }}',
            selectedService: '{{ request('service') ?? '' }}',
            link: '',
            quantity: '',
            services: [],
            selectedServiceData: null,
            loading: {
                services: false
            },
            errors: {},
            total: 0,
            submitting: false,
            dripfeed: {
                enabled: false,
                runs: 1,
                interval: 60
            },
            dripfeedPanel: false,
            subscription: {
                enabled: false,
                expiry: ''
            },
            custom_comments: '',

            init() {
                @if(request('service'))
                    this.loadServices();
                @endif
            },

            async loadServices() {
                if (!this.selectedCategory) {
                    this.services = [];
                    return;
                }

                this.loading.services = true;
                this.selectedService = '';
                this.selectedServiceData = null;

                try {
                    const response = await fetch('/api/services?category=' + this.selectedCategory);
                    const data = await response.json();
                    this.services = data;
                } catch (error) {
                    toastr.error('Failed to load services');
                } finally {
                    this.loading.services = false;
                }
            },

            async loadServiceDetails() {
                if (!this.selectedService) {
                    this.selectedServiceData = null;
                    return;
                }

                const service = this.services.find(s => s.id == this.selectedService);
                if (service) {
                    this.selectedServiceData = service;
                    this.quantity = service.min;
                    this.calculateTotal();
                }
            },

            calculateTotal() {
                if (this.selectedServiceData && this.quantity) {
                    const pricePerUnit = this.selectedServiceData.price / 1000;
                    let baseTotal = this.quantity * pricePerUnit;

                    if (this.dripfeed.enabled && this.dripfeed.runs > 1) {
                        baseTotal = baseTotal * this.dripfeed.runs;
                    }

                    this.total = Math.max(0, baseTotal);
                } else {
                    this.total = 0;
                }
            },

            async submitOrder() {
                this.errors = {};
                this.submitting = true;

                try {
                    const response = await fetch('{{ route('user.orders.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            service_id: this.selectedService,
                            link: this.link,
                            quantity: this.quantity,
                            dripfeed: this.dripfeed.enabled ? {
                                runs: this.dripfeed.runs,
                                interval: this.dripfeed.interval
                            } : null,
                            subscription: this.subscription.enabled ? {
                                expiry: this.subscription.expiry
                            } : null,
                            custom_comments: this.custom_comments || null
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        document.getElementById('newOrderId').textContent = data.order_id;
                        document.getElementById('viewOrderBtn').href = '/user/orders/' + data.order_id;
                        new bootstrap.Modal(document.getElementById('successModal')).show();
                        this.resetForm();
                    } else {
                        if (data.errors) {
                            this.errors = data.errors;
                        }
                        toastr.error(data.message || 'Failed to place order');
                    }
                } catch (error) {
                    toastr.error('An error occurred. Please try again.');
                } finally {
                    this.submitting = false;
                }
            },

            resetForm() {
                this.selectedService = '';
                this.selectedServiceData = null;
                this.link = '';
                this.quantity = '';
                this.total = 0;
                this.dripfeed.enabled = false;
                this.dripfeedPanel = false;
                this.subscription.enabled = false;
                this.custom_comments = '';
            }
        }
    }
</script>
@endpush
