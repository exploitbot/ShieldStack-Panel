# Quick Start Guide - AppsForte AI Enhanced

## ⚡ 60-Second Setup

### Step 1: Load Models in LM Studio (30 seconds)

1. Open LM Studio
2. Load your main LLM (e.g., Llama, Mistral, Qwen)
3. Load an embedding model (e.g., `nomic-embed-text-v1.5` or `all-MiniLM-L6-v2`)
4. Start the API server
5. Verify both endpoints work:
   ```bash
   curl http://localhost:1234/v1/models
   ```

### Step 2: Install Dependencies (15 seconds)

```bash
pip3 install requests
```

Optional for Windows SSH:
```bash
pip install paramiko
```

### Step 3: Run It! (15 seconds)

```bash
cd /var/www/html/embedded
./appsforte_ai_embedded.py
```

**That's it!** You're now running AppsForte AI with embedding-powered efficiency!

---

## 🎯 Your First Test

Try this to see semantic caching in action:

```
You: check disk space

[AI executes: df -h]
Output: Disk usage details...

You: show me the disk usage

✨ Cache hit! (similarity: 0.89) Using cached result
[Instant result - no re-execution!]
```

The embedding model recognized these are semantically similar!

---

## 🔧 Configuration (Optional)

Edit `config.json` if you want to tweak settings:

```json
{
  "enable_embeddings": true,
  "similarity_threshold": 0.75,
  "max_cache_entries": 100
}
```

**Default settings work great for most use cases!**

---

## 📚 Full Documentation

- **README.md** - Complete user guide
- **FEATURES.md** - Technical details and features
- **config.json** - Configuration options

---

## ❓ Troubleshooting

### Not seeing cache hits?

Lower the similarity threshold:
```json
"similarity_threshold": 0.65
```

### Embedding API not working?

Check if embedding model is loaded in LM Studio:
```bash
curl http://localhost:1234/v1/embeddings \
  -H "Content-Type: application/json" \
  -d '{"model":"local-model","input":"test"}'
```

### Want to disable embeddings?

```json
"enable_embeddings": false
```

Script will work normally without caching features.

---

## 🎉 You're All Set!

The enhanced version keeps all original features:
- ✅ SSH helper (no terminal lock)
- ✅ Thinking output
- ✅ TODO lists
- ✅ Environment sourcing
- ✅ Universal confirmation (Y/N/A/S)
- ✅ Loop detection
- ✅ Error recovery

**PLUS** new embedding-powered features:
- ✨ Semantic caching
- ✨ Smart context
- ✨ Intelligent memory

Enjoy your more efficient AI assistant!
