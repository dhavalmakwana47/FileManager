<div class="modal-header bg-primary text-white">
    <h5 class="modal-title" id="folderModalLabel"><i class="fas fa-folder-edit"></i>{{  $title}}</h5>
    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <div class="mb-3">
        <label for="folderName" class="form-label fw-bold"><i class="fas fa-folder"></i> File Name</label>
        <input type="text" class="form-control form-control-lg" id="folderName" name="name"
            value="{{ isset($file->name) ? $file->name : '' }}" required placeholder="Enter folder name..." disabled>
    </div>

    @include('app.folder.filepermissions')
</div>

<div class="modal-footer bg-light">
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ isset($file) ? 'Update' : 'Add' }}</button>
</div>
