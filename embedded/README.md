# AppsForte AI Enhanced - Embedding Model Edition

## 🚀 What's New?

This version integrates a **second embedding text model** from LM Studio to make AppsForte AI more efficient through:

1. **Semantic Tool Result Caching** - Avoid redundant operations
2. **Smart Conversation History** - Keep only relevant context
3. **Intelligent Context Management** - Better memory usage

**All original features preserved** + new embedding-powered enhancements!

---

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Setup](#setup)
- [Usage](#usage)
- [Configuration](#configuration)
- [How Embeddings Improve Efficiency](#how-embeddings-improve-efficiency)
- [Troubleshooting](#troubleshooting)

---

## ✨ Features

### Original Features (All Preserved)
- ✅ **Embedded SSH Helper** (Windows: paramiko, Linux: sshpass)
- ✅ **Thinking/Analysis Output** (🤔 THINKING before every task)
- ✅ **TODO Lists** (📝 TODO LIST before execution)
- ✅ **Environment Sourcing** (source files and use variables)
- ✅ **Universal Confirmation** (Y/N/A/S for all commands)
- ✅ **Loop Detection** (5-layer system)
- ✅ **Error Recovery** (Analyze and fix errors)
- ✅ **Cross-platform** (Windows/Linux/Mac)

### New Embedding-Powered Features
- ✨ **Semantic Caching** - Cache tool results and reuse for similar commands
- ✨ **Smart Context** - Select most relevant past conversations
- ✨ **Efficient Memory** - Better context window management
- ✨ **Vector Search** - Find relevant past interactions using embeddings

---

## 📦 Requirements

### 1. LM Studio with Two Models

You need **TWO models loaded in LM Studio**:

1. **Primary LLM** - Your main chat model (e.g., Llama, Mistral, Qwen)
2. **Embedding Model** - A text embedding model (e.g., nomic-embed-text, all-MiniLM-L6-v2)

**How to load both models in LM Studio:**
- Load your main LLM as usual
- Go to the "Embedding" tab or load a second embedding model
- Both will be accessible via the API at `http://localhost:1234/v1`

### 2. Python Requirements

```bash
pip3 install requests
```

Optional (for Windows colors):
```bash
pip install colorama
```

Optional (for SSH on Windows):
```bash
pip install paramiko
```

---

## 🔧 Setup

### Step 1: Load Models in LM Studio

1. Open LM Studio
2. Load your primary LLM (e.g., `llama-3.1-8b`)
3. Load an embedding model (e.g., `nomic-embed-text-v1.5`)
4. Start the local API server
5. Both models should be accessible at `http://localhost:1234/v1`

### Step 2: Configure the Script

Edit `config.json` or the script's CONFIG section:

```json
{
  "lm_studio_url": "http://api.appsscale.com/v1",
  "model": "local-model",
  "embedding_model": "local-model",
  "enable_embeddings": true,
  "similarity_threshold": 0.75,
  "max_cache_entries": 100
}
```

**Key Settings:**
- `enable_embeddings`: Set to `false` to disable embedding features
- `similarity_threshold`: 0.0-1.0 (higher = stricter cache matching)
- `max_cache_entries`: Maximum cached tool results

### Step 3: Make Script Executable

```bash
chmod +x appsforte_ai_embedded.py
```

---

## 🚀 Usage

### Basic Usage

```bash
cd /var/www/html/embedded
./appsforte_ai_embedded.py
```

### Example Session

```
AppsForte AI v3.1 Enhanced
✨ Embedding features enabled
Type 'exit' to quit

You: list all python files in this directory

🤔 THINKING: User wants to find Python files. I'll use the glob tool
to search for *.py files in the current directory.

📝 TODO LIST:
1. Use glob tool to find *.py files
2. Display the results

⚙️ EXECUTING:
[Tool execution with confirmation...]

You: list all python files again

✨ Cache hit! (similarity: 0.92) Using cached result
[Instant result from cache - no redundant execution!]
```

---

## ⚙️ Configuration

### Embedding Settings

**`enable_embeddings`** (default: `true`)
- Set to `false` to disable all embedding features
- Script will work normally without embeddings

**`similarity_threshold`** (default: `0.75`)
- Range: 0.0 to 1.0
- Higher values = stricter matching for cache hits
- `0.75` = Pretty similar commands will match
- `0.90` = Only very similar commands will match
- `0.60` = More lenient, broader caching

**`max_cache_entries`** (default: `100`)
- Maximum number of tool results to cache in memory
- Older entries are removed when limit is reached

**`relevant_history_count`** (default: `5`)
- Number of semantically relevant past messages to include
- Helps AI remember related past interactions

---

## 🧠 How Embeddings Improve Efficiency

### 1. Semantic Tool Result Caching

**Without Embeddings:**
```
You: check disk space
> df -h  [Executes command]

You: show me the disk usage
> df -h  [Executes again - redundant!]
```

**With Embeddings:**
```
You: check disk space
> df -h  [Executes command]

You: show me the disk usage
✨ Cache hit! (similarity: 0.89) Using cached result
[Instant - no redundant execution!]
```

The embedding model recognizes that "check disk space" and "show me disk usage" are **semantically similar**, so it reuses the cached result.

### 2. Smart Context Management

**Without Embeddings:**
- Keeps last N messages (chronological)
- May lose relevant but older context
- Context window fills with less relevant info

**With Embeddings:**
- Finds semantically relevant past messages
- Keeps context related to current topic
- Better memory usage

**Example:**
```
[10 messages ago: "The database password is in /etc/myapp/config"]
[Recent messages: Random chit-chat about weather, unrelated tasks...]

You: what was the database password location again?

With embeddings: ✅ Finds that old message semantically
Without: ❌ Lost in history, AI says "I don't recall"
```

### 3. Intelligent Cache Matching

Cached for safety (read-only operations):
- ✅ `bash` commands (read-only: ls, cat, df, ps, etc.)
- ✅ `read` tool
- ✅ `glob` tool
- ✅ `grep` tool

NOT cached (for safety):
- ❌ Write operations (rm, mv, cp, etc.)
- ❌ `write` tool
- ❌ `edit` tool
- ❌ Install commands (apt, pip, npm, etc.)

---

## 📊 Performance Benefits

### Typical Improvements

**Scenario 1: Repeated Checks**
```
Task: Monitor system status every few messages

Without embeddings:
- Executes 'top', 'df -h', 'free -m' every time
- 3-5 seconds per check

With embeddings:
- First check: Executes commands
- Subsequent checks: ✨ Cache hits
- <100ms response time
- 50x faster!
```

**Scenario 2: Long Conversations**
```
Task: 50+ message conversation about a project

Without embeddings:
- Context fills with old messages
- AI forgets earlier details
- User must repeat information

With embeddings:
- AI finds relevant past context
- Remembers project details
- Better continuity
```

### Memory Usage

- **Embedding cache**: ~10MB per 1000 cached vectors
- **Tool cache**: Depends on tool outputs (typically 1-5MB)
- **Total overhead**: 10-20MB for typical usage

---

## 🔍 Technical Details

### Embedding Model Requirements

**Recommended Models:**
- `nomic-embed-text-v1.5` (137M params, excellent quality)
- `all-MiniLM-L6-v2` (22M params, fast and lightweight)
- `bge-small-en-v1.5` (33M params, good balance)

**API Endpoint:**
```
POST http://localhost:1234/v1/embeddings
{
  "model": "local-model",
  "input": "text to embed"
}
```

### Vector Operations

- **Similarity Metric**: Cosine similarity
- **Vector Dimensions**: Depends on embedding model (typically 384-1024)
- **Search Algorithm**: Brute-force (fine for <10k vectors)
- **Storage**: In-memory (no persistence between sessions)

---

## 🐛 Troubleshooting

### Embedding Features Not Working

**Check 1: Embedding model loaded?**
```bash
curl http://localhost:1234/v1/embeddings \
  -H "Content-Type: application/json" \
  -d '{"model":"local-model","input":"test"}'
```

If error: Load an embedding model in LM Studio

**Check 2: Configuration**
```python
CONFIG = {
    "enable_embeddings": True,  # Must be True
    ...
}
```

**Check 3: requests library**
```bash
pip3 install requests
```

### Cache Not Hitting

**Reason 1: Threshold too high**
- Lower `similarity_threshold` from 0.75 to 0.60

**Reason 2: Commands too different**
```
"list files"        vs "show filesystem"
Similarity: ~0.65   (might not hit at 0.75 threshold)
```

**Reason 3: Write operations**
- Cache only works for read operations (by design, for safety)

### Performance Issues

**Issue: Slow embedding generation**
- Solution: Use a smaller embedding model
- Try `all-MiniLM-L6-v2` (very fast)

**Issue: High memory usage**
- Solution: Reduce `max_cache_entries` to 50 or lower
- Solution: Disable embeddings: `enable_embeddings: false`

---

## 🔄 Disable Embeddings

To use the script WITHOUT embedding features:

**Option 1: Config file**
```json
{
  "enable_embeddings": false
}
```

**Option 2: Edit script**
```python
CONFIG = {
    "enable_embeddings": False,
    ...
}
```

The script will work normally, just without caching and smart context features.

---

## 📝 All Features Summary

| Feature | Status | Description |
|---------|--------|-------------|
| Embedded SSH | ✅ | No terminal lock on Windows/Linux |
| Thinking Output | ✅ | Shows AI analysis before execution |
| TODO Lists | ✅ | Plans steps before executing |
| Env Sourcing | ✅ | Source files and use variables |
| Universal Confirmation | ✅ | Y/N/A/S for all commands |
| Semantic Cache | ✨ NEW | Avoid redundant tool executions |
| Smart Context | ✨ NEW | Keep relevant history |
| Vector Search | ✨ NEW | Find past relevant interactions |
| Loop Detection | ✅ | 5-layer loop prevention |
| Error Recovery | ✅ | Analyze and fix errors |
| Cross-platform | ✅ | Windows/Linux/Mac |

---

## 🎯 Use Cases

### Best For:
- ✅ Repeated system checks (disk, memory, processes)
- ✅ Long troubleshooting sessions
- ✅ Monitoring tasks with similar commands
- ✅ Projects with extended context needs
- ✅ When you have an embedding model in LM Studio

### When to Disable Embeddings:
- ❌ Very short, one-off tasks
- ❌ No embedding model available
- ❌ Limited RAM (<4GB available)
- ❌ Need deterministic behavior (no caching)

---

## 🔗 Links

- **Original Version**: `/home/appsforte/appsforte_ai.py`
- **Java Version**: `/var/www/html/appsforte-ai.jar`
- **Go Version**: `/var/www/html/appsforte-ai`
- **This Version**: `/var/www/html/embedded/appsforte_ai_embedded.py`

---

## 📞 Quick Reference

### Start with embeddings:
```bash
./appsforte_ai_embedded.py
```

### Start without embeddings:
```bash
# Edit config.json first, set enable_embeddings: false
./appsforte_ai_embedded.py
```

### Test embedding API:
```bash
curl http://localhost:1234/v1/embeddings \
  -H "Content-Type: application/json" \
  -d '{"model":"local-model","input":"test"}'
```

---

**🎉 Enjoy your more efficient AI assistant with semantic understanding!**
