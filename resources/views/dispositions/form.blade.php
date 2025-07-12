@extends('layouts.app')

@section('title', isset($disposition) ? 'Edit Disposition' : 'Create Disposition')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>{{ isset($disposition) ? 'Edit Disposition' : 'Create New Disposition' }}</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ isset($disposition) ? route('dispositions.update', $disposition->id) : route('dispositions.store') }}">
                        @csrf
                        @if(isset($disposition))
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" 
                                   value="{{ old('name', $disposition->name ?? '') }}" 
                                   required maxlength="50">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="color">Color *</label>
                            <input type="color" class="form-control @error('color') is-invalid @enderror" 
                                   id="color" name="color" 
                                   value="{{ old('color', $disposition->color ?? '#4f46e5') }}" 
                                   required>
                            @error('color')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" 
                                      rows="3" maxlength="255">{{ old('description', $disposition->description ?? '') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="sort_order">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" name="sort_order" 
                                   value="{{ old('sort_order', $disposition->sort_order ?? 0) }}" 
                                   min="0">
                            @error('sort_order')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" 
                                       id="is_active" name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $disposition->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                {{ isset($disposition) ? 'Update' : 'Create' }} Disposition
                            </button>
                            <a href="{{ route('dispositions.dashboard') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
