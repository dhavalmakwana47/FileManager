<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">{{ $extraOptions['title'] ?? 'Data Table' }}</h3>
    </div>
    <div class="card-body">
        <table id="{{ $id }}" class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ $column['title'] }}</th>
                    @endforeach
                </tr>
            </thead>
        </table>
    </div>
</div>
