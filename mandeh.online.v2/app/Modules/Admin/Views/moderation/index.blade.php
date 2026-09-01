<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Moderation Queue') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metadata</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($queue as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->interaction->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->interaction->entity->name }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $item->interaction->content }}</div>
                                <div class="text-xs text-gray-500">Score: {{ $item->interaction->rating_score }}/5</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs text-gray-500">GPS: {{ $item->interaction->gps_lat }}, {{ $item->interaction->gps_lng }}</div>
                                <div class="text-xs text-red-500">Unverified Presence</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form action="{{ route('admin.moderation.decide', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="decision" value="approve">
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900 mr-4">Approve</button>
                                </form>
                                <form action="{{ route('admin.moderation.decide', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="decision" value="reject">
                                    <button type="submit" class="text-red-600 hover:text-red-900">Reject</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $queue->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
