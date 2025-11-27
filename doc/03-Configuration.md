# Configuration <a id="configuration"></a>

## Overview

The AskAI module requires configuration before it can be used. All configuration is done through the Icinga Web 2 interface under **Configuration → Modules → askai → Configuration**.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [AI API Provider Setup](#ai-api-provider-setup)
3. [Module Configuration](#module-configuration)
4. [Configuration Options](#configuration-options)
5. [Configuration File](#configuration-file)
6. [Debug Mode](#debug-mode)

---

## Prerequisites <a id="prerequisites"></a>

Before configuring the AskAI module, ensure:

- The AskAI module is installed and enabled
- You have administrative access to Icinga Web 2
- Internet connectivity is available for AI API calls
- You have obtained an API key from a supported AI provider

---

## AI API Provider Setup <a id="ai-api-provider-setup"></a>

### OpenRouter (Recommended)

OpenRouter provides free access to multiple AI models. To get started:

1. Visit [https://openrouter.ai](https://openrouter.ai)
2. Sign up for a free account
3. Navigate to **Keys** section in your dashboard
4. Generate a new API key
5. Copy the API key (you'll need this for configuration)

**Free Models Available:**
- X/Grok 4.1 Fast
- OpenAI/GPT-OSS 20b
- TNG/R1T Chimera

### Other ChatGPT-Compatible APIs

The module supports any ChatGPT-compatible API endpoint that accepts the following request format:

```json
{
  "model": "model-name",
  "messages": [
    {"role": "system", "content": "instructions"},
    {"role": "user", "content": "prompt"}
  ]
}
```

### Payload customization
If your AI provider expects different payload, it can be customized in [Index Controller](../application/controllers/IndexController.php). The payload can be found in the `sendToAi($prompt)` function. 

In the same file the LLM response extraction happens in the return statement. If the response differs from the default, it can be customized as well.

---

## Module Configuration <a id="module-configuration"></a>

### Web Interface Configuration

1. Log in to Icinga Web 2 as an administrator
2. Navigate to **Configuration → Modules → askai**
3. Click on the **Configuration** tab
4. Fill in the required fields (see [Configuration Options](#configuration-options))
5. Click **Save Changes**

### Configuration Location

The module configuration is stored in:
```
/etc/icingaweb2/modules/askai/config.ini
```

**Note:** It's recommended to use the web interface for configuration rather than editing the file directly.

---

## Configuration Options <a id="configuration-options"></a>

### API Endpoint

**Field Name:** `API Endpoint`  
**Required:** Yes  
**Type:** URL

The full URL to the AI API endpoint.

**Examples:**
```
https://openrouter.ai/api/v1/chat/completions
https://api.openai.com/v1/chat/completions
https://your-custom-api.example.com/v1/chat/completions
```

**Validation:**
- Must be a valid URL
- Must start with `http://` or `https://`
- HTTPS is strongly recommended for production use

---

### API Key

**Field Name:** `API Key`  
**Required:** Yes  
**Type:** Password (masked)

Your authentication key for the AI API.

**Important Notes:**
- The API key is stored as a password field
- The actual key is stored in the configuration file
- Never share your API key publicly
- Rotate your API key if compromised

**Security:**
- Transmitted over HTTPS when making API calls
- Not visible in debug output

---

### AI Model

**Field Name:** `AI Model`  
**Required:** Yes  
**Type:** Dropdown selection

Select which AI model to use for generating troubleshooting suggestions.

**Available Models (OpenRouter):**

| Model | Description | Best For |
|-------|-------------|----------|
| `x-ai/grok-4.1-fast:free` | X/Grok 4.1 Fast | Fast, real-time troubleshooting with minimal latency |
| `openai/gpt-oss-20b:free` | OpenAI/GPT-OSS 20b | Detailed, thorough analysis and complex scenarios |
| `tngtech/tng-r1t-chimera:free` | TNG/R1T Chimera | Balanced performance for general troubleshooting |


**Choosing a Model:**
- **For most users:** Start with X/Grok 4.1 Fast (good balance of speed and quality)
- **For detailed analysis:** Use OpenAI/GPT-OSS 20b
- **For cost-conscious environments:** Use TNG/R1T Chimera

**Custom Model**
A new feature has been introduced: choosing your own model. In order to choose one, choose the last option in the list: `Custom Model`. A new text input will be revealed where the name of a model is expected.

---

### Enable Custom Instructions

**Field Name:** `Enable Custom Instructions`  
**Required:** No  
**Type:** Checkbox  
**Default:** Disabled

When enabled, allows you to provide custom system instructions for the AI assistant instead of using the default system prompt.

**Use Cases:**
- Customize AI behavior for your specific environment
- Add organization-specific knowledge or procedures
- Adjust response format or style
- Include additional context about your infrastructure

**Behavior:**
- When unchecked: Uses the built-in expert troubleshooting instructions
- When checked: Reveals the "Custom Instructions" text area below

---

### Custom Instructions (Content)

**Field Name:** `Custom Instructions`  
**Required:** No (only visible when "Enable Custom Instructions" is checked)  
**Type:** Textarea

Enter custom system instructions for the AI assistant. This completely replaces the default instructions.

**Default Instructions (for reference):**
The module includes comprehensive default instructions that guide the AI to:
- Act as an expert Icinga monitoring troubleshooting assistant
- Provide step-by-step troubleshooting in numbered format
- Focus on actionable solutions through Icinga Director
- Analyze service states and error patterns
- Consider distributed monitoring architectures
- Avoid suggesting direct filesystem modifications

**Custom Instructions Example:**
```
You are an expert troubleshooting assistant for our company's Icinga monitoring system.

Our environment:
- Distributed monitoring with 5 satellite zones
- All configurations managed through Icinga Director
- Primary focus on web services and database monitoring
- Standard response time threshold: 5 seconds

When troubleshooting:
1. Always check satellite connectivity first
2. Verify service dependencies in Director
3. Check our internal knowledge base at https://wiki.company.com
4. Provide 3-5 actionable steps
5. Include expected outcomes for each step
6. Reference our incident response procedures when applicable

Format: Plain text, numbered steps, concise but thorough.
```

**Best Practices:**
- Keep instructions focused and specific
- Avoid overly verbose instructions (AI may become less effective)
- Test custom instructions with various scenarios
- Document your custom instructions for team reference

---

### Enable Debug Mode

**Field Name:** `Enable Debug Mode`  
**Required:** No  
**Type:** Checkbox  
**Default:** Disabled

When enabled, displays diagnostic information on the AI response page.

**Debug Information Includes:**
- Given Instructions (default or custom)
- Prompt sent to the AI
- Model name used

**When to Enable:**
- Testing new configurations
- Troubleshooting API issues
- Developing custom instructions
- Understanding AI behavior

**When to Disable:**
- Production use
- When sharing AI responses with users
- To reduce visual clutter

**Note:** Debug information is shown at the top of the AI response page before the troubleshooting suggestions.

---

## Configuration File <a id="configuration-file"></a>

### File Location

```
/etc/icingaweb2/modules/askai/config.ini
```

### File Format

The configuration is stored in INI format under the `[ai]` section and it's completely managed by the Icingaweb2:

```ini
[ai]
endpoint = "https://openrouter.ai/api/v1/chat/completions"
apikey = "sk-or-v1-xxxxxxxxxxxxxxxxxxxxx"
model = "x-ai/grok-4.1-fast:free"
custom_instructions = "0"
enable_debug = "0"
```

---

## Example Configurations

### Example 1: OpenRouter with X/Grok 4.1 Fast (Recommended for Beginners)

```ini
[ai]
endpoint = "https://openrouter.ai/api/v1/chat/completions"
apikey = "sk-or-v1-xxxxxxxxxxxxxxxxxxxxx"
model = "x-ai/grok-4.1-fast:free"
custom_instructions = "0"
enable_debug = "0"
```

### Example 2: Custom Instructions with Debug Mode

```ini
[ai]
endpoint = "https://openrouter.ai/api/v1/chat/completions"
apikey = "sk-or-v1-xxxxxxxxxxxxxxxxxxxxx"
model = "openai/gpt-oss-20b:free"
custom_instructions = "1"
custom_instructions_content = "You are an expert troubleshooting assistant for our company's Icinga monitoring system."
enable_debug = "1"
```