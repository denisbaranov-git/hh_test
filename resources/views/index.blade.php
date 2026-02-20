<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@3.2.47/dist/vue.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>


<div class="container mt-5 position-relative" id="app">
    <style>
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .loading-overlay {
            position: fixed; /* Изменено с absolute на fixed */
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
    </style>
    <div v-if="isLoading" style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:1000">
        <div class="spinner-border text-primary" style="width:3rem; height:3rem"></div>
    </div>
    <div :class="{'opacity-50': isLoading}">
        <div class="row mb-4">
            <div class="col">
                <h1></h1>
            </div>
            <div class="col-auto">
                <button @click="showCreateModal = true" class="btn btn-primary">Созать новый</button>
                <button @click="generateDocuments" class="btn btn-success">Сгенерировать 1000</button>
                <button @click="clearDocuments" class="btn btn-danger">Удалить все документы</button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" v-model="googleSheetUrlVal" placeholder="Google Sheet URL">
                    <button @click="saveGoogleSheetUrl" class="btn btn-sm btn-warning">Set URL</button>
                </div>
                <button @click="syncDocumentsToGoogle" class="btn btn-secondary w-max">Синхронизировать->Google sheet</button>
            </div>
            <a href="{{route('fetch.data',['count'=>'20'])}}">{{route('fetch.data')}} </a>
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="document in documents" :key="document.id">
                <td>@{{ document.id }}</td>
                <td>@{{ document.name }}</td>
                <td>@{{ document.description }}</td>
                <td>
                <span :class="{'badge bg-success': document.status === 'Allowed', 'badge bg-danger': document.status === 'Prohibited'}">
                    @{{ document.status }}
                </span>
                </td>
                <td>
                    <button @click="editDocument(document)" class="btn btn-sm btn-warning">Edit</button>
                    <button @click="deleteDocument(document.id)" class="btn btn-sm btn-danger">Delete</button>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Create/Edit Modal -->
        <div class="modal fade" :class="{show: showCreateModal}" tabindex="-1" style="display: block;" v-if="showCreateModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@{{ editingDocument.id ? 'Редактировать' : 'Создать' }}</h5>
                        <button type="button" class="btn-close" @click="showCreateModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" v-model="editingDocument.name">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="description" v-model="editingDocument.description">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" v-model="editingDocument.status">
                                <option value="Allowed">Allowed</option>
                                <option value="Prohibited">Prohibited</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showCreateModal = false">Close</button>
                        <button type="button" class="btn btn-primary" @click="saveDocument">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade" :class="{show: showCreateModal}" v-if="showCreateModal"></div>
    </div>
</div>

<script>
    const { createApp, ref, onMounted } = Vue;

    createApp({
        setup() {
            const documents = ref([]);
            const showCreateModal = ref(false);
            const editingDocument = ref({
                id: null,
                name: '',
                description: '',
                status: 'Prohibited'
            });
            const googleSheetUrlVal = ref('');
            const isLoading = ref(false);

            const fetchDocuments = async () => {
                isLoading.value = true;
                try {
                    const response = await axios.get('/api/documents');
                    documents.value = response.data.documents;
                    googleSheetUrlVal.value = response.data.googleSheetUrlVal;
                } catch (error) {
                    console.error(error);
                    alert('Ошибка получения документов');
                }
                finally {
                    isLoading.value = false;
                }
            };

            const editDocument = (document) => {
                editingDocument.value = { ...document };
                showCreateModal.value = true;
            };

            const saveDocument = async () => {
                try {
                    if (editingDocument.value.id) {
                        await axios.put(`/api/documents/${editingDocument.value.id}`, editingDocument.value, editingDocument.description);
                    } else {
                        await axios.post('/api/documents', editingDocument.value);
                    }
                    await fetchDocuments();
                    showCreateModal.value = false;
                    editingDocument.value = { id: null, name: '', description: '', status: 'Prohibited' };
                } catch (error) {
                    console.error(error);
                    alert('Ошибка сохранения документов');
                }
            };

            const deleteDocument = async (id) => {
                if (!confirm('Удалить документ?')) return;

                try {
                    await axios.delete(`/api/documents/${id}`);
                    await fetchDocuments();
                } catch (error) {
                    console.error(error);
                    alert('Ошибка удаления документа');
                }
            };

            const generateDocuments = async () => {
                if (!confirm('Создать 1000 новых документов?')) return;
                isLoading.value = true;
                try {
                    await axios.post('/api/documents/generate');
                    await fetchDocuments();
                    alert('1000 документов создано!');
                } catch (error) {
                    console.error(error);
                    alert('Ошибка создания новых документов');
                }
                finally {
                    isLoading.value = false;
                }
            };

            const clearDocuments = async () => {
                if (!confirm('Удалить все документы?')) return;

                try {
                    await axios.post('/api/documents/clear');
                    await fetchDocuments();
                    alert('Все документы удалены');
                } catch (error) {
                    console.error(error);
                    alert('Ошибка удаления документов');
                }
            };

            const syncDocumentsToGoogle = async () => {
                if (!confirm('Сиинхронизация на google sheets?')) return;

                try {
                    await axios.get('/api/google-sheet/sync');
                    await fetchDocuments();
                    alert('Синхронизация OK!');
                } catch (error) {
                    console.error(error);
                    alert('Ошибка Синхронизации google');
                }
            };

            const saveGoogleSheetUrl = async () => {
                try {
                    await axios.post('/api/google-sheet/set-url', {'url':googleSheetUrlVal.value});
                    //await fetchDocuments();
                } catch (error) {
                    console.error(error);
                }
            };

            onMounted(fetchDocuments);

            return {
                documents,
                showCreateModal,
                editingDocument,
                editDocument,
                saveDocument,
                deleteDocument,
                generateDocuments,
                clearDocuments,
                syncDocumentsToGoogle,
                googleSheetUrlVal,
                saveGoogleSheetUrl,
                isLoading
            };
        }
    }).mount('#app');
</script>
</body>
</html>
