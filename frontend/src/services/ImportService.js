import axios from '../config/axiosConf'

export const uploadFile = async (file) => {
  const formData = new FormData()
  formData.append('file', file)
  
  const response = await axios.post('/api/import/upload', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
  
  return response.data
}

export const saveMappings = async (mappingName, mappings) => {
  const response = await axios.post('/api/import/mappings', {
    mapping_name: mappingName,
    mappings
  })
  
  return response.data
}

export const getMappings = async () => {
  const response = await axios.get('/api/import/mappings')
  return response.data
}

export const deleteMapping = async (id) => {
  const response = await axios.delete(`/api/import/mappings/${id}`)
  return response.data
}

export const importTransactions = async (filePath, mappings) => {
  const response = await axios.post('/api/import/transactions', {
    file_path: filePath,
    mappings
  })
  
  return response.data
} 