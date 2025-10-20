# AI Timetable Generator Integration

## Overview
The system now includes an AI-powered timetable generator that uses OpenAI's GPT-4 to create optimal timetables.

## Setup

### 1. Environment Variables
Add these to your `.env` file:

```env
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_API_URL=https://api.openai.com/v1/chat/completions
```

### 2. Features

#### AI-Powered Generation
- **Intelligent Scheduling**: Uses GPT-4 to analyze constraints and generate optimal timetables
- **Smart Room Assignment**: AI considers room types, capacity, and lab requirements
- **Lecturer Workload Balancing**: Distributes teaching load evenly across lecturers
- **Conflict Avoidance**: Prevents double-booking of rooms and lecturers
- **Time Optimization**: Prefers morning slots for theory and afternoon for labs

#### Fallback System
- **Automatic Fallback**: If AI generation fails, automatically falls back to rule-based generation
- **Error Handling**: Comprehensive error logging and user feedback
- **Reliability**: Ensures timetables are always generated, even if AI is unavailable

### 3. How It Works

1. **Data Collection**: Gathers all available rooms, lecturers, and unit assignments
2. **AI Prompt**: Creates a detailed prompt with all constraints and requirements
3. **AI Processing**: Sends request to OpenAI GPT-4 for intelligent scheduling
4. **Response Parsing**: Validates and processes AI-generated schedule
5. **Database Storage**: Creates timetable entries in the database
6. **Fallback**: Uses rule-based generation if AI fails

### 4. AI Prompt Structure

The AI receives:
- Available rooms with capacity and type
- Available lecturers
- Units to schedule with requirements
- Time slots and days
- Scheduling rules and constraints

### 5. Benefits

- **Better Optimization**: AI considers multiple factors simultaneously
- **Smarter Scheduling**: Understands context and relationships
- **Reduced Conflicts**: Better conflict detection and avoidance
- **Improved Efficiency**: More optimal use of resources
- **Adaptive**: Can handle complex scheduling scenarios

### 6. Usage

The AI generator is automatically used when generating timetables. Users will see:
- "AI generated X entries successfully!" for successful AI generation
- "AI generation failed. Used fallback method." if AI fails but fallback works
- Error messages if both AI and fallback fail

### 7. Configuration

The AI generator can be configured in `config/services.php`:
- API key and URL settings
- Model selection (currently GPT-4)
- Temperature and token limits
- Timeout settings

### 8. Monitoring

All AI generation attempts are logged with:
- Success/failure status
- Number of entries generated
- Error messages and stack traces
- Performance metrics

## Requirements

- OpenAI API key
- Internet connection for AI API calls
- Fallback system for offline operation
