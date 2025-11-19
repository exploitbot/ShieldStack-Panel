#!/usr/bin/env python3
"""
AppsForte AI - Enhanced with Embedding Model Support
Uses LM Studio's embedding API for semantic search, caching, and smart context management
All original features preserved + new embedding-powered efficiency improvements
"""

import os
import sys
import json
import subprocess
import re
import fnmatch
import shutil
import platform
import math
from pathlib import Path
from typing import Dict, List, Any, Optional, Tuple

# Try to import readline for input history (Unix/Linux/Mac only)
try:
    import readline
    READLINE_AVAILABLE = True
except ImportError:
    READLINE_AVAILABLE = False

# Try to import requests
try:
    import requests
    REQUESTS_AVAILABLE = True
except ImportError:
    REQUESTS_AVAILABLE = False

# Enable ANSI colors on Windows
if platform.system().lower() == 'windows':
    try:
        import colorama
        colorama.init()
        COLORAMA_AVAILABLE = True
    except ImportError:
        COLORAMA_AVAILABLE = False
else:
    COLORAMA_AVAILABLE = True

# Configuration - Now with embedding support
CONFIG = {
    "lm_studio_url": "http://api.appsscale.com/v1",
    "model": "local-model",
    "embedding_model": "text-embedding-nomic-embed-text-v1.5",  # Embedding model from LM Studio
    "temperature": 0.7,
    "max_tokens": 4096,
    "enable_embeddings": True,  # Set to False to disable embedding features
    "similarity_threshold": 0.75,  # Cosine similarity threshold for caching
    "max_cache_entries": 100,  # Maximum cached tool results
    "relevant_history_count": 5,  # Number of relevant past messages to include
}

# ANSI color codes
class Colors:
    if COLORAMA_AVAILABLE:
        BLUE = '\033[94m'
        GREEN = '\033[92m'
        YELLOW = '\033[93m'
        RED = '\033[91m'
        CYAN = '\033[96m'
        MAGENTA = '\033[95m'
        RESET = '\033[0m'
        BOLD = '\033[1m'
        DIM = '\033[2m'
    else:
        BLUE = GREEN = YELLOW = RED = CYAN = MAGENTA = RESET = BOLD = DIM = ''


# ============================================================================
# EMBEDDING MODEL CLIENT
# ============================================================================

class EmbeddingClient:
    """Client for LM Studio embedding API"""

    def __init__(self, api_url: str, model: str):
        self.api_url = api_url
        self.model = model
        self.cache = {}  # Simple in-memory cache for embeddings

    def get_embedding(self, text: str) -> Optional[List[float]]:
        """Get embedding vector for text"""
        if not CONFIG["enable_embeddings"] or not REQUESTS_AVAILABLE:
            return None

        # Check cache first
        if text in self.cache:
            return self.cache[text]

        try:
            response = requests.post(
                f"{self.api_url}/embeddings",
                headers={"Content-Type": "application/json"},
                json={
                    "model": self.model,
                    "input": text
                },
                timeout=30
            )
            response.raise_for_status()

            data = response.json()
            if 'data' in data and len(data['data']) > 0:
                embedding = data['data'][0]['embedding']
                # Cache the result
                self.cache[text] = embedding
                return embedding

            return None

        except Exception as e:
            print(f"{Colors.DIM}[Embedding] Warning: {str(e)}{Colors.RESET}")
            return None

    def get_batch_embeddings(self, texts: List[str]) -> List[Optional[List[float]]]:
        """Get embeddings for multiple texts"""
        return [self.get_embedding(text) for text in texts]


# ============================================================================
# VECTOR OPERATIONS & STORAGE
# ============================================================================

class VectorStore:
    """Simple in-memory vector store with cosine similarity search"""

    @staticmethod
    def cosine_similarity(vec1: List[float], vec2: List[float]) -> float:
        """Calculate cosine similarity between two vectors"""
        if not vec1 or not vec2 or len(vec1) != len(vec2):
            return 0.0

        dot_product = sum(a * b for a, b in zip(vec1, vec2))
        mag1 = math.sqrt(sum(a * a for a in vec1))
        mag2 = math.sqrt(sum(b * b for b in vec2))

        if mag1 == 0 or mag2 == 0:
            return 0.0

        return dot_product / (mag1 * mag2)

    def __init__(self):
        self.vectors = []  # List of (id, vector, metadata) tuples
        self.next_id = 0

    def add(self, vector: List[float], metadata: Dict[str, Any]) -> int:
        """Add a vector with metadata, return ID"""
        vec_id = self.next_id
        self.next_id += 1
        self.vectors.append((vec_id, vector, metadata))
        return vec_id

    def search(self, query_vector: List[float], top_k: int = 5, threshold: float = 0.0) -> List[Tuple[int, float, Dict]]:
        """Search for similar vectors, return [(id, score, metadata), ...]"""
        if not query_vector:
            return []

        results = []
        for vec_id, vector, metadata in self.vectors:
            similarity = self.cosine_similarity(query_vector, vector)
            if similarity >= threshold:
                results.append((vec_id, similarity, metadata))

        # Sort by similarity (highest first)
        results.sort(key=lambda x: x[1], reverse=True)
        return results[:top_k]

    def remove(self, vec_id: int):
        """Remove a vector by ID"""
        self.vectors = [(id, vec, meta) for id, vec, meta in self.vectors if id != vec_id]

    def clear(self):
        """Clear all vectors"""
        self.vectors = []
        self.next_id = 0

    def size(self) -> int:
        """Get number of vectors stored"""
        return len(self.vectors)


# ============================================================================
# TOOL RESULT CACHE (Powered by Embeddings)
# ============================================================================

class ToolResultCache:
    """Cache tool results with semantic similarity matching"""

    def __init__(self, embedding_client: EmbeddingClient, max_entries: int = 100):
        self.embedding_client = embedding_client
        self.vector_store = VectorStore()
        self.max_entries = max_entries
        self.results = {}  # id -> result mapping
        self.enabled = CONFIG["enable_embeddings"]

    def _create_cache_key(self, tool_name: str, arguments: Dict[str, Any]) -> str:
        """Create a semantic cache key"""
        if tool_name == "bash":
            return f"bash: {arguments.get('command', '')}"
        elif tool_name == "read":
            return f"read: {arguments.get('file_path', '')}"
        elif tool_name == "glob":
            return f"glob: {arguments.get('pattern', '')} in {arguments.get('path', '.')}"
        elif tool_name == "grep":
            return f"grep: {arguments.get('pattern', '')} in {arguments.get('path', '.')}"
        else:
            return f"{tool_name}: {json.dumps(arguments, sort_keys=True)}"

    def get(self, tool_name: str, arguments: Dict[str, Any]) -> Optional[Dict[str, Any]]:
        """Get cached result if similar command was executed recently"""
        if not self.enabled:
            return None

        # Only cache read-only operations for safety
        if tool_name not in ["bash", "read", "glob", "grep"]:
            return None

        # Don't cache write operations in bash
        if tool_name == "bash":
            cmd = arguments.get('command', '').lower()
            write_keywords = ['rm', 'delete', 'mv', 'cp', 'write', '>', 'wget', 'curl', 'install', 'update']
            if any(kw in cmd for kw in write_keywords):
                return None

        cache_key = self._create_cache_key(tool_name, arguments)
        query_embedding = self.embedding_client.get_embedding(cache_key)

        if not query_embedding:
            return None

        # Search for similar cached operations
        results = self.vector_store.search(
            query_embedding,
            top_k=1,
            threshold=CONFIG["similarity_threshold"]
        )

        if results:
            vec_id, similarity, metadata = results[0]
            cached_result = self.results.get(vec_id)

            if cached_result:
                print(f"{Colors.MAGENTA}✨ Cache hit! (similarity: {similarity:.2f}) Using cached result{Colors.RESET}")
                return cached_result

        return None

    def put(self, tool_name: str, arguments: Dict[str, Any], result: Dict[str, Any]):
        """Cache a tool result"""
        if not self.enabled or not result.get("success"):
            return

        # Only cache read-only operations
        if tool_name not in ["bash", "read", "glob", "grep"]:
            return

        # Don't cache write operations in bash
        if tool_name == "bash":
            cmd = arguments.get('command', '').lower()
            write_keywords = ['rm', 'delete', 'mv', 'cp', 'write', '>', 'wget', 'curl', 'install', 'update']
            if any(kw in cmd for kw in write_keywords):
                return

        cache_key = self._create_cache_key(tool_name, arguments)
        embedding = self.embedding_client.get_embedding(cache_key)

        if not embedding:
            return

        # Add to vector store
        metadata = {
            "tool_name": tool_name,
            "arguments": arguments,
            "cache_key": cache_key
        }
        vec_id = self.vector_store.add(embedding, metadata)
        self.results[vec_id] = result

        # Enforce max size (remove oldest if over limit)
        if self.vector_store.size() > self.max_entries:
            oldest_id = min(self.results.keys())
            self.vector_store.remove(oldest_id)
            del self.results[oldest_id]


# ============================================================================
# SEMANTIC CONVERSATION HISTORY MANAGER
# ============================================================================

class SemanticHistoryManager:
    """Manage conversation history with semantic search"""

    def __init__(self, embedding_client: EmbeddingClient):
        self.embedding_client = embedding_client
        self.vector_store = VectorStore()
        self.messages = []  # All messages with their IDs
        self.next_id = 0
        self.enabled = CONFIG["enable_embeddings"]

    def add_message(self, message: Dict[str, str]):
        """Add a message to history"""
        msg_id = self.next_id
        self.next_id += 1
        self.messages.append((msg_id, message))

        # Create embedding for user and assistant messages
        if self.enabled and message["role"] in ["user", "assistant"]:
            content = message.get("content", "")
            if content:
                embedding = self.embedding_client.get_embedding(content)
                if embedding:
                    metadata = {
                        "msg_id": msg_id,
                        "role": message["role"],
                        "content": content
                    }
                    self.vector_store.add(embedding, metadata)

    def get_relevant_history(self, current_message: str, top_k: int = 5) -> List[Dict[str, str]]:
        """Get most relevant past messages based on semantic similarity"""
        if not self.enabled:
            return []

        query_embedding = self.embedding_client.get_embedding(current_message)
        if not query_embedding:
            return []

        # Search for relevant past interactions
        results = self.vector_store.search(query_embedding, top_k=top_k, threshold=0.5)

        # Extract messages
        relevant_messages = []
        for _, similarity, metadata in results:
            msg_id = metadata["msg_id"]
            # Find the message by ID
            for stored_id, msg in self.messages:
                if stored_id == msg_id:
                    relevant_messages.append(msg)
                    break

        return relevant_messages

    def get_recent_messages(self, count: int = 10) -> List[Dict[str, str]]:
        """Get most recent messages"""
        recent = [msg for _, msg in self.messages[-count:]]
        return recent

    def clear(self):
        """Clear all history"""
        self.messages = []
        self.vector_store.clear()
        self.next_id = 0


# ============================================================================
# SSH EXECUTE FUNCTION (From original - preserved)
# ============================================================================

def ssh_execute(host: str, user: str, password: str, command: str, port: int = 22) -> Dict[str, Any]:
    """Platform-aware SSH command execution - Uses paramiko on Windows, sshpass on Linux/Mac"""
    system = platform.system().lower()

    if system == 'windows':
        try:
            import paramiko
            client = paramiko.SSHClient()
            client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

            try:
                client.connect(
                    hostname=host,
                    username=user,
                    password=password,
                    port=port,
                    timeout=30,
                    look_for_keys=False,
                    allow_agent=False
                )

                stdin, stdout, stderr = client.exec_command(command)
                output = stdout.read().decode('utf-8', errors='replace')
                error = stderr.read().decode('utf-8', errors='replace')
                exit_code = stdout.channel.recv_exit_status()

                client.close()

                return {
                    "success": exit_code == 0,
                    "output": output,
                    "error": error,
                    "exit_code": exit_code
                }

            except Exception as e:
                client.close()
                return {
                    "success": False,
                    "output": "",
                    "error": f"SSH connection failed: {str(e)}",
                    "exit_code": 1
                }

        except ImportError:
            try:
                cmd = f'echo y | plink -pw {password} -P {port} {user}@{host} "{command}"'
                result = subprocess.run(
                    cmd,
                    shell=True,
                    capture_output=True,
                    text=True,
                    timeout=30
                )

                return {
                    "success": result.returncode == 0,
                    "output": result.stdout,
                    "error": result.stderr,
                    "exit_code": result.returncode
                }
            except Exception as e:
                return {
                    "success": False,
                    "output": "",
                    "error": f"SSH execution failed: {str(e)}",
                    "exit_code": 1
                }
    else:
        try:
            cmd = f"sshpass -p '{password}' ssh -T -n -o StrictHostKeyChecking=no -p {port} {user}@{host} \"{command}\""
            result = subprocess.run(
                cmd,
                shell=True,
                capture_output=True,
                text=True,
                timeout=30
            )

            return {
                "success": result.returncode == 0,
                "output": result.stdout,
                "error": result.stderr,
                "exit_code": result.returncode
            }
        except Exception as e:
            return {
                "success": False,
                "output": "",
                "error": f"SSH execution failed: {str(e)}",
                "exit_code": 1
            }


# ============================================================================
# DEPENDENCY CHECKER (From original - preserved)
# ============================================================================

class DependencyChecker:
    """Check and validate system dependencies"""

    @staticmethod
    def detect_os() -> Dict[str, str]:
        """Detect the operating system and package manager"""
        system = platform.system().lower()

        os_info = {
            "system": system,
            "release": platform.release(),
            "version": platform.version(),
            "machine": platform.machine(),
            "package_manager": None,
            "python_version": platform.python_version()
        }

        if system == "linux":
            if shutil.which("apt"):
                os_info["package_manager"] = "apt"
                os_info["distro"] = "debian/ubuntu"
            elif shutil.which("dnf"):
                os_info["package_manager"] = "dnf"
                os_info["distro"] = "fedora/rhel"
            elif shutil.which("yum"):
                os_info["package_manager"] = "yum"
                os_info["distro"] = "centos/rhel"
            elif shutil.which("pacman"):
                os_info["package_manager"] = "pacman"
                os_info["distro"] = "arch"
            elif shutil.which("zypper"):
                os_info["package_manager"] = "zypper"
                os_info["distro"] = "opensuse"
        elif system == "darwin":
            os_info["package_manager"] = "brew" if shutil.which("brew") else None
            os_info["distro"] = "macos"
        elif system == "windows":
            os_info["package_manager"] = "choco" if shutil.which("choco") else "pip"
            os_info["distro"] = "windows"

        return os_info

    @staticmethod
    def check_python_packages() -> Dict[str, Any]:
        """Check if required Python packages are installed"""
        required = {
            "requests": REQUESTS_AVAILABLE
        }

        recommended = {}
        if platform.system().lower() == 'windows':
            recommended["colorama"] = COLORAMA_AVAILABLE

        missing = [pkg for pkg, available in required.items() if not available]
        missing_recommended = [pkg for pkg, available in recommended.items() if not available]

        return {
            "all_installed": len(missing) == 0,
            "missing": missing,
            "missing_recommended": missing_recommended,
            "installed": [pkg for pkg, available in required.items() if available]
        }

    @staticmethod
    def get_install_instructions(os_info: Dict[str, str], missing_packages: List[str]) -> str:
        """Generate OS-specific installation instructions"""
        if not missing_packages:
            return ""

        instructions = f"\n{Colors.YELLOW}═══ Missing Dependencies ═══{Colors.RESET}\n\n"
        instructions += f"Missing Python packages: {', '.join(missing_packages)}\n\n"
        instructions += f"{Colors.CYAN}Installation Instructions:{Colors.RESET}\n\n"
        instructions += f"{Colors.GREEN}pip3 install {' '.join(missing_packages)}{Colors.RESET}\n\n"

        return instructions

    @staticmethod
    def run_preflight_checks() -> Tuple[bool, str]:
        """Run all preflight checks"""
        print(f"{Colors.CYAN}Running pre-flight checks...{Colors.RESET}\n")

        os_info = DependencyChecker.detect_os()
        print(f"OS: {os_info['system']} ({os_info.get('distro', 'unknown')})")
        print(f"Python: {os_info['python_version']}")

        if CONFIG["enable_embeddings"]:
            print(f"{Colors.MAGENTA}✨ Embedding features: ENABLED{Colors.RESET}")
        else:
            print(f"{Colors.DIM}Embedding features: disabled{Colors.RESET}")

        pkg_check = DependencyChecker.check_python_packages()

        if pkg_check["all_installed"]:
            print(f"{Colors.GREEN}✓ All required dependencies installed{Colors.RESET}\n")
            return True, ""
        else:
            print(f"{Colors.RED}✗ Missing required dependencies{Colors.RESET}")
            instructions = DependencyChecker.get_install_instructions(os_info, pkg_check["missing"])
            return False, instructions


# [CONTINUED IN NEXT PART - File is getting long]


# ============================================================================
# TOOL EXECUTOR (From original - preserved)
# ============================================================================

class ToolExecutor:
    """Handles execution of various tools"""

    def __init__(self):
        self.working_dir = os.getcwd()
        self.last_error = None

    def bash(self, command: str, description: str = "", interactive: bool = True) -> Dict[str, Any]:
        """Execute bash commands"""
        print(f"{Colors.CYAN}> {command}{Colors.RESET}")

        try:
            if interactive:
                result = subprocess.run(
                    command,
                    shell=True,
                    cwd=self.working_dir,
                    text=True,
                    stdin=None,
                    capture_output=False
                )

                if result.returncode == 0:
                    return {
                        "success": True,
                        "message": f"Command executed successfully",
                        "command": command,
                        "returncode": 0
                    }
                else:
                    return {
                        "success": False,
                        "error": f"Command failed with exit code: {result.returncode}",
                        "command": command,
                        "returncode": result.returncode
                    }
            else:
                result = subprocess.run(
                    command,
                    shell=True,
                    cwd=self.working_dir,
                    capture_output=True,
                    text=True,
                    timeout=300
                )

                if result.returncode == 0:
                    return {
                        "success": True,
                        "output": result.stdout.strip(),
                        "stderr": result.stderr.strip(),
                        "command": command,
                        "returncode": 0
                    }
                else:
                    return {
                        "success": False,
                        "error": f"Command failed with exit code: {result.returncode}",
                        "stdout": result.stdout.strip(),
                        "stderr": result.stderr.strip(),
                        "command": command,
                        "returncode": result.returncode
                    }

        except Exception as e:
            return {
                "success": False,
                "error": str(e),
                "command": command
            }

    def read(self, file_path: str, offset: int = 0, limit: int = 2000) -> Dict[str, Any]:
        """Read file contents"""
        try:
            file_path = os.path.normpath(os.path.expanduser(file_path))

            if not os.path.exists(file_path):
                return {
                    "success": False,
                    "error": f"Path does not exist: {file_path}"
                }

            if not os.path.isfile(file_path):
                return {
                    "success": False,
                    "error": f"Path is not a file: {file_path}"
                }

            with open(file_path, 'r', encoding='utf-8', errors='replace') as f:
                lines = f.readlines()

            total_lines = len(lines)
            lines_to_show = lines[offset:offset + limit if limit else None]

            numbered_lines = []
            for i, line in enumerate(lines_to_show, start=offset + 1):
                if len(line) > 2000:
                    line = line[:2000] + "... [truncated]\n"
                numbered_lines.append(f"{i:6d}\t{line.rstrip()}")

            content = "\n".join(numbered_lines)

            return {
                "success": True,
                "content": content,
                "total_lines": total_lines,
                "showing_lines": f"{offset + 1}-{offset + len(lines_to_show)}",
                "file_path": file_path
            }
        except Exception as e:
            return {
                "success": False,
                "error": f"Error reading file: {str(e)}",
                "file_path": file_path
            }

    def write(self, file_path: str, content: str) -> Dict[str, Any]:
        """Write content to a file"""
        try:
            file_path = os.path.normpath(os.path.expanduser(file_path))
            parent_dir = os.path.dirname(file_path)
            if parent_dir and not os.path.exists(parent_dir):
                os.makedirs(parent_dir, exist_ok=True)

            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)

            return {
                "success": True,
                "message": f"Successfully wrote to {file_path}",
                "bytes_written": len(content.encode('utf-8')),
                "file_path": file_path
            }
        except Exception as e:
            return {
                "success": False,
                "error": f"Error writing file: {str(e)}",
                "file_path": file_path
            }

    def edit(self, file_path: str, old_string: str, new_string: str, replace_all: bool = False) -> Dict[str, Any]:
        """Edit file by replacing strings"""
        try:
            file_path = os.path.normpath(os.path.expanduser(file_path))
            if not os.path.isfile(file_path):
                return {
                    "success": False,
                    "error": f"File not found: {file_path}"
                }

            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()

            if old_string not in content:
                return {
                    "success": False,
                    "error": "old_string not found in file",
                    "file_path": file_path
                }

            if not replace_all and content.count(old_string) > 1:
                return {
                    "success": False,
                    "error": f"old_string appears {content.count(old_string)} times. Use replace_all=True",
                    "occurrences": content.count(old_string)
                }

            if replace_all:
                new_content = content.replace(old_string, new_string)
                replacements = content.count(old_string)
            else:
                new_content = content.replace(old_string, new_string, 1)
                replacements = 1

            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(new_content)

            return {
                "success": True,
                "message": f"Successfully replaced {replacements} occurrence(s)",
                "replacements": replacements,
                "file_path": file_path
            }
        except Exception as e:
            return {
                "success": False,
                "error": f"Error editing file: {str(e)}",
                "file_path": file_path
            }

    def glob(self, pattern: str, path: str = ".") -> Dict[str, Any]:
        """Find files matching a pattern"""
        try:
            path = os.path.normpath(os.path.expanduser(path))

            if not os.path.exists(path):
                return {
                    "success": False,
                    "error": f"Search path does not exist: {path}"
                }

            matches = []

            if "**" in pattern:
                for root, dirs, files in os.walk(path):
                    dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['node_modules', '__pycache__', 'venv']]
                    rel_root = os.path.relpath(root, path)
                    for filename in files:
                        rel_path = os.path.join(rel_root, filename)
                        if rel_root == '.':
                            rel_path = filename
                        if fnmatch.fnmatch(rel_path, pattern) or fnmatch.fnmatch(filename, pattern.split('/')[-1]):
                            matches.append(os.path.join(root, filename))
            else:
                import glob as glob_module
                matches = glob_module.glob(os.path.join(path, pattern), recursive=True)

            matches.sort(key=lambda x: os.path.getmtime(x) if os.path.exists(x) else 0, reverse=True)

            return {
                "success": True,
                "matches": matches[:100],
                "total_matches": len(matches),
                "pattern": pattern,
                "search_path": path
            }
        except Exception as e:
            return {
                "success": False,
                "error": f"Error during glob search: {str(e)}",
                "pattern": pattern
            }

    def grep(self, pattern: str, path: str = ".", glob_pattern: str = None,
             case_insensitive: bool = False, output_mode: str = "files_with_matches",
             context_lines: int = 0, show_line_numbers: bool = False) -> Dict[str, Any]:
        """Search for pattern in files"""
        try:
            path = os.path.normpath(os.path.expanduser(path))
            results = []

            if glob_pattern:
                glob_result = self.glob(glob_pattern, path)
                if not glob_result["success"]:
                    return glob_result
                files_to_search = glob_result["matches"]
            elif os.path.isfile(path):
                files_to_search = [path]
            else:
                files_to_search = []
                for root, dirs, files in os.walk(path):
                    dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['node_modules', '__pycache__', 'venv']]
                    for filename in files:
                        if not filename.startswith('.'):
                            files_to_search.append(os.path.join(root, filename))

            flags = re.IGNORECASE if case_insensitive else 0
            try:
                regex = re.compile(pattern, flags)
            except re.error as e:
                return {
                    "success": False,
                    "error": f"Invalid regex pattern: {e}",
                    "pattern": pattern
                }

            for file_path in files_to_search[:500]:
                try:
                    with open(file_path, 'r', encoding='utf-8', errors='replace') as f:
                        lines = f.readlines()

                    matches_in_file = []
                    for i, line in enumerate(lines, start=1):
                        if regex.search(line):
                            matches_in_file.append((i, line.rstrip()))

                    if matches_in_file:
                        if output_mode == "files_with_matches":
                            results.append(file_path)
                        elif output_mode == "content":
                            for line_num, line_content in matches_in_file:
                                if show_line_numbers:
                                    results.append(f"{file_path}:{line_num}: {line_content}")
                                else:
                                    results.append(f"{file_path}: {line_content}")
                        elif output_mode == "count":
                            results.append(f"{file_path}: {len(matches_in_file)}")
                except:
                    continue

            return {
                "success": True,
                "results": results[:200],
                "total_results": len(results),
                "pattern": pattern,
                "search_path": path
            }
        except Exception as e:
            return {
                "success": False,
                "error": f"Error during grep search: {str(e)}",
                "pattern": pattern
            }


# Tool definitions
TOOLS = [
    {
        "type": "function",
        "function": {
            "name": "bash",
            "description": "Execute a bash command",
            "parameters": {
                "type": "object",
                "properties": {
                    "command": {"type": "string", "description": "The bash command"},
                    "description": {"type": "string", "description": "Brief description"},
                    "interactive": {"type": "boolean", "description": "Interactive mode", "default": True}
                },
                "required": ["command"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "read",
            "description": "Read file contents with line numbers",
            "parameters": {
                "type": "object",
                "properties": {
                    "file_path": {"type": "string", "description": "File path"},
                    "offset": {"type": "integer", "description": "Start line", "default": 0},
                    "limit": {"type": "integer", "description": "Number of lines", "default": 2000}
                },
                "required": ["file_path"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "write",
            "description": "Write content to file",
            "parameters": {
                "type": "object",
                "properties": {
                    "file_path": {"type": "string", "description": "File path"},
                    "content": {"type": "string", "description": "File content"}
                },
                "required": ["file_path", "content"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "edit",
            "description": "Edit file by replacing strings",
            "parameters": {
                "type": "object",
                "properties": {
                    "file_path": {"type": "string", "description": "File path"},
                    "old_string": {"type": "string", "description": "String to replace"},
                    "new_string": {"type": "string", "description": "Replacement string"},
                    "replace_all": {"type": "boolean", "description": "Replace all occurrences", "default": False}
                },
                "required": ["file_path", "old_string", "new_string"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "glob",
            "description": "Find files matching pattern",
            "parameters": {
                "type": "object",
                "properties": {
                    "pattern": {"type": "string", "description": "Glob pattern"},
                    "path": {"type": "string", "description": "Search directory", "default": "."}
                },
                "required": ["pattern"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "grep",
            "description": "Search for pattern in files",
            "parameters": {
                "type": "object",
                "properties": {
                    "pattern": {"type": "string", "description": "Search pattern"},
                    "path": {"type": "string", "description": "Search path", "default": "."},
                    "glob_pattern": {"type": "string", "description": "File filter"},
                    "case_insensitive": {"type": "boolean", "default": False},
                    "output_mode": {"type": "string", "enum": ["files_with_matches", "content", "count"], "default": "files_with_matches"},
                    "show_line_numbers": {"type": "boolean", "default": False}
                },
                "required": ["pattern"]
            }
        }
    }
]



# ============================================================================
# MAIN AI AGENT (Enhanced with Embeddings)
# ============================================================================

class AppsForteAI:
    """Main agent with embedding-powered enhancements"""

    def __init__(self):
        self.tool_executor = ToolExecutor()
        self.conversation_history = []
        self.user_preferences = {}
        self.require_confirmation = True
        
        # Initialize embedding components
        self.embedding_client = EmbeddingClient(CONFIG["lm_studio_url"], CONFIG["embedding_model"])
        self.tool_cache = ToolResultCache(self.embedding_client, CONFIG["max_cache_entries"])
        self.history_manager = SemanticHistoryManager(self.embedding_client)
        
        # Loop detection (from original)
        self.recent_tool_calls = []
        self.loop_threshold = 2
        self.consecutive_errors = 0
        self.max_consecutive_errors = 3
        
        # Build system prompt (same as original + embedding info)
        os_info = DependencyChecker.detect_os()
        os_name = os_info['system'].upper()
        os_distro = os_info.get('distro', 'unknown')
        
        embedding_status = "ENABLED" if CONFIG["enable_embeddings"] else "DISABLED"
        
        self.system_prompt = f"""You are AppsForte AI v3.1 Enhanced with Embedding Support.

EMBEDDING FEATURES ({embedding_status}):
- Semantic search of conversation history
- Intelligent tool result caching (avoid redundant operations)
- Smart context management

You have access to various tools for file operations and command execution.

🎯 MANDATORY WORKFLOW - FOLLOW FOR EVERY REQUEST:

1. 📋 ANALYZE & THINK FIRST:
   - Output: "🤔 THINKING: [Your analysis]"
   - This MUST be first before any tool use

2. ✅ CREATE TODO LIST:
   - Output: "📝 TODO LIST:\\n1. [step]\\n2. [step]..."
   - Show before execution

3. ⚙️ EXECUTE WITH CONFIRMATION:
   - User must confirm EVERY command with Y/N/A
   - Show what you're doing before asking

ENVIRONMENT SOURCING:
- Read env file first
- Use: bash tool with "source /path/file && command"
- Example: source /u01/EBSapps.env && echo $ORACLE_HOME

EMBEDDED SSH HELPER:
- Windows: Uses paramiko (no terminal lock)
- Linux/Mac: Uses sshpass with -T -n flags

CRITICAL RULES:
- Stop on errors - analyze and fix before continuing
- Never repeat same tool call
- No interactive programs (vim, top, mysql alone, etc.)
- Use -e, -c, --eval flags for database CLIs
- OS: {os_name} ({os_distro})

Always be helpful and thorough."""

    def trim_conversation_history(self, messages: List[Dict], max_messages: int = 50) -> List[Dict]:
        """Trim conversation with semantic relevance"""
        if len(messages) <= max_messages:
            return messages

        system_msg = [msg for msg in messages if msg["role"] == "system"]
        other_msgs = [msg for msg in messages if msg["role"] != "system"]
        
        # If embeddings enabled, try to keep semantically relevant messages
        if CONFIG["enable_embeddings"] and len(other_msgs) > 0:
            # Get the most recent user message
            recent_user_msgs = [msg for msg in other_msgs if msg["role"] == "user"]
            if recent_user_msgs:
                last_user_msg = recent_user_msgs[-1].get("content", "")
                relevant = self.history_manager.get_relevant_history(last_user_msg, top_k=max_messages // 2)
                
                # Combine relevant history + recent messages
                recent_msgs = other_msgs[-max_messages // 2:]
                combined = relevant + recent_msgs
                
                # Remove duplicates while preserving order
                seen = set()
                unique_msgs = []
                for msg in combined:
                    msg_str = json.dumps(msg, sort_keys=True)
                    if msg_str not in seen:
                        seen.add(msg_str)
                        unique_msgs.append(msg)
                
                print(f"{Colors.MAGENTA}✨ Smart context: {len(unique_msgs)} relevant messages selected{Colors.RESET}")
                return system_msg + unique_msgs[-max_messages:]
        
        # Fallback to simple recent messages
        recent_msgs = other_msgs[-max_messages:]
        print(f"{Colors.YELLOW}⚠ Context trimmed (keeping last {max_messages} messages){Colors.RESET}")
        return system_msg + recent_msgs

    def call_api(self, messages: List[Dict], tools: List[Dict] = None, stream: bool = True) -> Any:
        """Call LM Studio API"""
        if not REQUESTS_AVAILABLE:
            return None

        headers = {"Content-Type": "application/json"}
        trimmed_messages = self.trim_conversation_history(messages)

        payload = {
            "model": CONFIG["model"],
            "messages": trimmed_messages,
            "temperature": CONFIG["temperature"],
            "max_tokens": CONFIG["max_tokens"],
            "stream": stream
        }

        if tools:
            payload["tools"] = tools
            payload["tool_choice"] = "auto"

        try:
            response = requests.post(
                f"{CONFIG['lm_studio_url']}/chat/completions",
                headers=headers,
                json=payload,
                stream=stream,
                timeout=300
            )
            response.raise_for_status()
            return response
        except Exception as e:
            print(f"{Colors.RED}✗ API error: {e}{Colors.RESET}")
            return None

    def is_sensitive_command(self, tool_name: str, arguments: Dict[str, Any]) -> tuple[bool, str]:
        """Determine if command is sensitive"""
        if tool_name == "bash":
            command = arguments.get("command", "").lower()
            
            critical = ["rm -rf", "shutdown", "reboot", "mkfs", "dd if=", ":(){ :|:& };:"]
            for pattern in critical:
                if pattern in command:
                    return (True, "CRITICAL")
            
            high_risk = ["sudo", "chmod", "chown", "apt", "yum", "dnf", "pip install"]
            for pattern in high_risk:
                if pattern in command:
                    return (True, "HIGH")
        
        elif tool_name in ["write", "edit"]:
            file_path = arguments.get("file_path", "")
            sensitive_paths = ["/etc/", "/sys/", "C:\\Windows\\"]
            for path in sensitive_paths:
                if path.lower() in file_path.lower():
                    return (True, "HIGH")
        
        return (False, "LOW")

    def get_user_confirmation(self, tool_name: str, arguments: Dict[str, Any], risk_level: str) -> str:
        """Ask user for confirmation"""
        print(f"\n{Colors.YELLOW}{'='*60}{Colors.RESET}")
        print(f"{Colors.YELLOW}📋 AI wants to execute:{Colors.RESET}\n")

        if tool_name == "bash":
            print(f"{Colors.CYAN}Command:{Colors.RESET} {arguments.get('command', '')}")
        elif tool_name == "write":
            print(f"{Colors.CYAN}Write to:{Colors.RESET} {arguments.get('file_path', '')}")
        elif tool_name == "edit":
            print(f"{Colors.CYAN}Edit:{Colors.RESET} {arguments.get('file_path', '')}")
        else:
            print(f"{Colors.CYAN}Tool:{Colors.RESET} {tool_name}")

        if risk_level == "CRITICAL":
            print(f"\n{Colors.RED}⚠️  CRITICAL RISK!{Colors.RESET}")
        elif risk_level == "HIGH":
            print(f"\n{Colors.YELLOW}⚠️  HIGH RISK{Colors.RESET}")

        print(f"\n{Colors.YELLOW}{'='*60}{Colors.RESET}")
        print(f"{Colors.GREEN}[Y]{Colors.RESET} Yes  {Colors.RED}[N]{Colors.RESET} No  {Colors.CYAN}[A]{Colors.RESET} Always  {Colors.CYAN}[S]{Colors.RESET} Never ask")
        print(f"{Colors.YELLOW}{'='*60}{Colors.RESET}\n")

        while True:
            try:
                choice = input(f"{Colors.BOLD}Your choice [Y/N/A/S]:{Colors.RESET} ").strip().lower()

                if choice in ['y', 'yes']:
                    return 'yes'
                elif choice in ['n', 'no']:
                    return 'no'
                elif choice in ['a', 'always']:
                    if tool_name == "bash":
                        command_type = arguments.get("command", "").split()[0]
                        self.user_preferences[f"bash:{command_type}"] = 'always'
                        print(f"{Colors.GREEN}✓ Will always allow '{command_type}'{Colors.RESET}")
                    else:
                        self.user_preferences[f"tool:{tool_name}"] = 'always'
                        print(f"{Colors.GREEN}✓ Will always allow '{tool_name}'{Colors.RESET}")
                    return 'yes'
                elif choice in ['s', 'skip']:
                    self.require_confirmation = False
                    print(f"{Colors.GREEN}✓ Confirmations disabled{Colors.RESET}")
                    return 'yes'
                else:
                    print(f"{Colors.YELLOW}Please enter Y, N, A, or S{Colors.RESET}")
            except (KeyboardInterrupt, EOFError):
                print(f"\n{Colors.YELLOW}Cancelled{Colors.RESET}")
                return 'no'

    def execute_tool(self, tool_name: str, arguments: Dict[str, Any]) -> Dict[str, Any]:
        """Execute tool with caching and confirmation"""
        
        # Check cache first (for read-only operations)
        if CONFIG["enable_embeddings"]:
            cached_result = self.tool_cache.get(tool_name, arguments)
            if cached_result:
                return cached_result
        
        # Confirmation check
        is_sensitive, risk_level = self.is_sensitive_command(tool_name, arguments)
        
        if tool_name == "bash":
            command_type = arguments.get("command", "").split()[0]
            pref_key = f"bash:{command_type}"
        else:
            pref_key = f"tool:{tool_name}"
        
        if self.require_confirmation:
            if pref_key in self.user_preferences and self.user_preferences[pref_key] == 'always':
                pass
            else:
                user_choice = self.get_user_confirmation(tool_name, arguments, risk_level)
                if user_choice == 'no':
                    return {
                        "success": False,
                        "error": "Command cancelled by user"
                    }
        
        # Loop detection
        if tool_name == "bash":
            core_sig = f"bash:{arguments.get('command', '')}"
        elif tool_name == "read":
            core_sig = f"read:{arguments.get('file_path', '')}"
        elif tool_name == "write":
            core_sig = f"write:{arguments.get('file_path', '')}"
        else:
            core_sig = f"{tool_name}:{json.dumps(arguments, sort_keys=True)}"
        
        self.recent_tool_calls.append(core_sig)
        if len(self.recent_tool_calls) > 20:
            self.recent_tool_calls.pop(0)
        
        # Simple loop detection
        if len(self.recent_tool_calls) >= self.loop_threshold:
            recent = self.recent_tool_calls[-self.loop_threshold:]
            if len(set(recent)) == 1:
                print(f"\n{Colors.RED}⚠ LOOP DETECTED!{Colors.RESET}\n")
                return {
                    "success": False,
                    "error": f"LOOP DETECTED - Repeated same action {self.loop_threshold} times",
                    "error_type": "infinite_loop"
                }
        
        # Execute the tool
        result = None
        try:
            if tool_name == "bash":
                result = self.tool_executor.bash(**arguments)
            elif tool_name == "read":
                result = self.tool_executor.read(**arguments)
            elif tool_name == "write":
                result = self.tool_executor.write(**arguments)
            elif tool_name == "edit":
                result = self.tool_executor.edit(**arguments)
            elif tool_name == "glob":
                result = self.tool_executor.glob(**arguments)
            elif tool_name == "grep":
                result = self.tool_executor.grep(**arguments)
            else:
                result = {"success": False, "error": f"Unknown tool: {tool_name}"}
        except Exception as e:
            result = {"success": False, "error": str(e)}
        
        # Cache successful results
        if CONFIG["enable_embeddings"] and result and result.get("success"):
            self.tool_cache.put(tool_name, arguments, result)
        
        # Track errors
        if not result.get("success"):
            print(f"{Colors.RED}✗ Tool failed: {result.get('error', 'Unknown')}{Colors.RESET}")
            self.consecutive_errors += 1
            
            if self.consecutive_errors >= self.max_consecutive_errors:
                print(f"\n{Colors.RED}⚠ TOO MANY ERRORS ({self.consecutive_errors})!{Colors.RESET}\n")
                result["error"] = f"Giving up after {self.consecutive_errors} consecutive failures"
        else:
            print(f"{Colors.GREEN}✓ Tool succeeded{Colors.RESET}")
            self.consecutive_errors = 0
        
        return result

    def process_streaming_response(self, response) -> tuple[str, List[Dict]]:
        """Process streaming API response"""
        content = ""
        tool_calls = []

        print(f"\n{Colors.GREEN}AppsForte AI:{Colors.RESET} ", end="", flush=True)

        for line in response.iter_lines():
            if not line:
                continue

            line = line.decode('utf-8')
            if not line.startswith('data: '):
                continue

            if line.strip() == 'data: [DONE]':
                break

            try:
                data = json.loads(line[6:])

                if 'choices' not in data or len(data['choices']) == 0:
                    continue

                choice = data['choices'][0]
                delta = choice.get('delta', {})

                if 'content' in delta and delta['content']:
                    chunk = delta['content']
                    content += chunk
                    print(chunk, end="", flush=True)

                if 'tool_calls' in delta:
                    for tc in delta['tool_calls']:
                        idx = tc.get('index', 0)

                        if len(tool_calls) <= idx:
                            tool_calls.append({
                                'id': tc.get('id', ''),
                                'type': tc.get('type', 'function'),
                                'function': {'name': '', 'arguments': ''}
                            })

                        if 'id' in tc:
                            tool_calls[idx]['id'] = tc['id']
                        if 'type' in tc:
                            tool_calls[idx]['type'] = tc['type']

                        if 'function' in tc:
                            func = tc['function']
                            if 'name' in func:
                                tool_calls[idx]['function']['name'] += func['name']
                            if 'arguments' in func:
                                tool_calls[idx]['function']['arguments'] += func['arguments']

            except json.JSONDecodeError:
                continue

        if content:
            print()

        return content, tool_calls

    def run(self):
        """Main conversation loop"""
        print(f"{Colors.BOLD}{Colors.CYAN}AppsForte AI v3.1 Enhanced{Colors.RESET}")
        if CONFIG["enable_embeddings"]:
            print(f"{Colors.MAGENTA}✨ Embedding features enabled{Colors.RESET}")
        print(f"{Colors.DIM}Type 'exit' to quit{Colors.RESET}\n")

        # Initialize with system prompt
        self.conversation_history.append({
            "role": "system",
            "content": self.system_prompt
        })

        while True:
            try:
                user_input = input(f"{Colors.BLUE}You:{Colors.RESET} ").strip()
            except (EOFError, KeyboardInterrupt):
                print(f"\n{Colors.YELLOW}Interrupted{Colors.RESET}\n")
                continue

            if not user_input:
                continue

            if user_input.lower() in ['exit', 'quit', 'bye']:
                print("Goodbye!")
                break

            # Add to history
            user_msg = {
                "role": "user",
                "content": user_input
            }
            self.conversation_history.append(user_msg)
            self.history_manager.add_message(user_msg)

            # Reset tracking
            self.recent_tool_calls = []
            self.consecutive_errors = 0

            # Agent loop
            max_iterations = 999
            iteration = 0
            interrupted = False

            while iteration < max_iterations and not interrupted:
                iteration += 1

                try:
                    response = self.call_api(self.conversation_history, TOOLS, stream=True)
                    if not response:
                        break

                    content, tool_calls = self.process_streaming_response(response)
                except KeyboardInterrupt:
                    print(f"\n{Colors.YELLOW}⚠ Interrupted{Colors.RESET}")
                    interrupted = True
                    break

                # Build assistant message
                assistant_message = {"role": "assistant"}
                if content:
                    assistant_message["content"] = content
                if tool_calls:
                    assistant_message["tool_calls"] = tool_calls

                if not content and not tool_calls:
                    assistant_message["content"] = "I apologize, but I didn't generate a proper response."

                self.conversation_history.append(assistant_message)
                self.history_manager.add_message(assistant_message)

                if not tool_calls:
                    break

                # Execute tools
                has_errors = False
                for tool_call in tool_calls:
                    func_name = tool_call['function']['name']

                    try:
                        func_args = json.loads(tool_call['function']['arguments'])
                    except json.JSONDecodeError:
                        func_args = {}

                    try:
                        result = self.execute_tool(func_name, func_args)
                    except KeyboardInterrupt:
                        print(f"\n{Colors.YELLOW}⚠ Tool interrupted{Colors.RESET}")
                        interrupted = True
                        break

                    if not result.get("success"):
                        has_errors = True

                    self.conversation_history.append({
                        "role": "tool",
                        "tool_call_id": tool_call['id'],
                        "name": func_name,
                        "content": json.dumps(result)
                    })

                if interrupted:
                    break

                if has_errors and iteration < max_iterations:
                    print(f"\n{Colors.YELLOW}⚠ Error detected, AI will address it...{Colors.RESET}")
                    continue

            if iteration >= max_iterations:
                print(f"\n{Colors.RED}⚠ Safety limit reached ({max_iterations} iterations){Colors.RESET}")

            print()


def main():
    """Entry point"""
    deps_ok, install_instructions = DependencyChecker.run_preflight_checks()

    if not deps_ok:
        print(install_instructions)
        sys.exit(1)

    # Test API connectivity
    if REQUESTS_AVAILABLE:
        try:
            response = requests.get(f"{CONFIG['lm_studio_url']}/models", timeout=5)
            response.raise_for_status()
            print(f"{Colors.GREEN}✓ Connected to LM Studio API{Colors.RESET}\n")
        except requests.exceptions.RequestException:
            print(f"{Colors.RED}✗ Cannot connect to API at {CONFIG['lm_studio_url']}{Colors.RESET}")
            sys.exit(1)

    agent = AppsForteAI()
    agent.run()


if __name__ == "__main__":
    main()
