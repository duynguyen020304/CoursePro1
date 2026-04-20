import { useReducer, useState, useRef, useCallback, useMemo, useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import { motion, AnimatePresence } from 'framer-motion';
import toast, { Toaster } from 'react-hot-toast';
import { createEditor, Editor, Transforms, Element as SlateElement, Text, Node, Descendant } from 'slate';
import { Slate, Editable, withReact, ReactEditor } from 'slate-react';
import {
  instructorApi,
  categoryApi,
  chapterApi,
  courseApi,
  lessonApi,
} from '../../../services/api';
import {
  uploadSingleVideoToS3,
  uploadMultipartVideoToS3,
} from '../../admin/uploadVideo.upload';

/* ================================================================
   Types
   ================================================================ */

interface Category {
  id: string;
  name: string;
}

interface ResourceDraft {
  _id: string;
  file: File;
  title: string;
  sort_order: number;
}

interface LessonDraft {
  _id: string;
  title: string;
  content: string;
  sort_order: number;
  isExpanded: boolean;
  videoFile: File | null;
  resources: ResourceDraft[];
}

interface ChapterDraft {
  _id: string;
  title: string;
  description: string;
  sort_order: number;
  isExpanded: boolean;
  lessons: LessonDraft[];
}

interface CourseFormState {
  title: string;
  description: string;
  price: string;
  difficulty: string;
  language: string;
  category_ids: string[];
  objectives: string[];
  requirements: string[];
  chapters: ChapterDraft[];
}

interface ProgressState {
  isSubmitting: boolean;
  currentStep: string;
  stepIndex: number;
  totalSteps: number;
  errors: string[];
  courseId?: string;
}

type FormAction =
  | { type: 'SET_FIELD'; field: keyof CourseFormState; value: string | string[] }
  | { type: 'ADD_ARRAY_ITEM'; field: 'objectives' | 'requirements' }
  | { type: 'REMOVE_ARRAY_ITEM'; field: 'objectives' | 'requirements'; index: number }
  | { type: 'UPDATE_ARRAY_ITEM'; field: 'objectives' | 'requirements'; index: number; value: string }
  | { type: 'ADD_CHAPTER' }
  | { type: 'REMOVE_CHAPTER'; index: number }
  | { type: 'UPDATE_CHAPTER'; index: number; field: 'title' | 'description'; value: string }
  | { type: 'TOGGLE_CHAPTER'; index: number }
  | { type: 'ADD_LESSON'; chapterIndex: number }
  | { type: 'REMOVE_LESSON'; chapterIndex: number; lessonIndex: number }
  | { type: 'UPDATE_LESSON'; chapterIndex: number; lessonIndex: number; field: 'title' | 'content'; value: string }
  | { type: 'TOGGLE_LESSON'; chapterIndex: number; lessonIndex: number }
  | { type: 'SET_VIDEO_FILE'; chapterIndex: number; lessonIndex: number; file: File | null }
  | { type: 'ADD_RESOURCE'; chapterIndex: number; lessonIndex: number; file: File }
  | { type: 'REMOVE_RESOURCE'; chapterIndex: number; lessonIndex: number; resourceIndex: number }
  | { type: 'RESET_STATE'; state: Partial<CourseFormState> };

interface Course {
  title?: string;
  description?: string | null;
  price?: number;
  difficulty?: string;
  language?: string;
  categories?: Array<{ id: string }>;
  objectives?: Array<{ objective?: string }>;
  requirements?: Array<{ requirement?: string }>;
  chapters?: ChapterData[];
}

interface ChapterData {
  chapter_id: string | number;
  title?: string;
  description?: string;
  sort_order?: number;
  lessons?: LessonData[];
}

interface LessonData {
  lesson_id: string | number;
  title?: string;
  content?: string;
  sort_order?: number;
}

interface CourseFormProps {
  mode: 'create' | 'edit';
  initialData?: Course;
  courseId?: string;
  onSuccess: () => void;
  onCancel: () => void;
}

/* ================================================================
   Reducer
   ================================================================ */

const initialState: CourseFormState = {
  title: '',
  description: '',
  price: '',
  difficulty: 'All Level',
  language: 'Vietnamese',
  category_ids: [],
  objectives: [''],
  requirements: [''],
  chapters: [],
};

function formReducer(state: CourseFormState, action: FormAction): CourseFormState {
  switch (action.type) {
    case 'SET_FIELD':
      return { ...state, [action.field]: action.value };
    case 'RESET_STATE':
      return { ...state, ...action.state };
    case 'ADD_ARRAY_ITEM':
      return { ...state, [action.field]: [...state[action.field], ''] };
    case 'REMOVE_ARRAY_ITEM': {
      const arr = [...state[action.field]];
      if (arr.length > 1) arr.splice(action.index, 1);
      return { ...state, [action.field]: arr };
    }
    case 'UPDATE_ARRAY_ITEM': {
      const arr = [...state[action.field]];
      arr[action.index] = action.value;
      return { ...state, [action.field]: arr };
    }
    case 'ADD_CHAPTER':
      return {
        ...state,
        chapters: [
          ...state.chapters,
          {
            _id: crypto.randomUUID(),
            title: '',
            description: '',
            sort_order: state.chapters.length,
            isExpanded: true,
            lessons: [],
          },
        ],
      };
    case 'REMOVE_CHAPTER':
      return { ...state, chapters: state.chapters.filter((_, i) => i !== action.index) };
    case 'UPDATE_CHAPTER': {
      const chapters = [...state.chapters];
      chapters[action.index] = { ...chapters[action.index], [action.field]: action.value };
      return { ...state, chapters };
    }
    case 'TOGGLE_CHAPTER': {
      const chapters = [...state.chapters];
      chapters[action.index] = { ...chapters[action.index], isExpanded: !chapters[action.index].isExpanded };
      return { ...state, chapters };
    }
    case 'ADD_LESSON': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [
        ...ch.lessons,
        {
          _id: crypto.randomUUID(),
          title: '',
          content: '',
          sort_order: ch.lessons.length,
          isExpanded: true,
          videoFile: null,
          resources: [],
        },
      ];
      ch.isExpanded = true;
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'REMOVE_LESSON': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = ch.lessons.filter((_, i) => i !== action.lessonIndex);
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'UPDATE_LESSON': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [...ch.lessons];
      ch.lessons[action.lessonIndex] = { ...ch.lessons[action.lessonIndex], [action.field]: action.value };
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'TOGGLE_LESSON': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [...ch.lessons];
      ch.lessons[action.lessonIndex] = { ...ch.lessons[action.lessonIndex], isExpanded: !ch.lessons[action.lessonIndex].isExpanded };
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'SET_VIDEO_FILE': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [...ch.lessons];
      ch.lessons[action.lessonIndex] = { ...ch.lessons[action.lessonIndex], videoFile: action.file };
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'ADD_RESOURCE': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [...ch.lessons];
      const lesson = { ...ch.lessons[action.lessonIndex] };
      lesson.resources = [
        ...lesson.resources,
        {
          _id: crypto.randomUUID(),
          file: action.file,
          title: action.file.name,
          sort_order: lesson.resources.length,
        },
      ];
      ch.lessons[action.lessonIndex] = lesson;
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'REMOVE_RESOURCE': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [...ch.lessons];
      const lesson = { ...ch.lessons[action.lessonIndex] };
      lesson.resources = lesson.resources.filter((_, i) => i !== action.resourceIndex);
      ch.lessons[action.lessonIndex] = lesson;
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    default:
      return state;
  }
}

/* ================================================================
   Validation
   ================================================================ */

function validateForm(state: CourseFormState): Record<string, string> {
  const errors: Record<string, string> = {};
  if (!state.title.trim()) errors.title = 'Course title is required';
  if (!state.description.trim()) errors.description = 'Description is required';
  else if (state.description.trim().length < 10) errors.description = 'Description must be at least 10 characters';
  if (!state.price.trim()) errors.price = 'Price is required';
  else if (isNaN(parseFloat(state.price)) || parseFloat(state.price) < 0) errors.price = 'Price must be a non-negative number';
  return errors;
}

/* ================================================================
   Helpers
   ================================================================ */

function extractId(response: { data: { data: unknown } }, field: string): string {
  const data = response.data.data as Record<string, unknown>;
  return String(data[field]);
}

function formatFileSize(bytes: number): string {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function calculateTotalSteps(state: CourseFormState): number {
  let steps = 1;
  state.chapters.forEach((ch) => {
    steps += 1;
    ch.lessons.forEach((ls) => {
      steps += 1;
      if (ls.videoFile) steps += 1;
      steps += ls.resources.length;
    });
  });
  return steps;
}

const VALID_VIDEO_TYPES = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
const MAX_VIDEO_SIZE = 500 * 1024 * 1024;

const SLATE_EMPTY: Descendant[] = [{ type: 'paragraph', children: [{ text: '' }] } as Descendant];

function escapeHtml(text: string): string {
  return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function serializeSlateToHtml(nodes: Descendant[]): string {
  return nodes.map(serializeNode).join('');
}

function serializeNode(node: Descendant): string {
  if (Text.isText(node)) {
    let text = escapeHtml(node.text);
    const marks = node as unknown as Record<string, unknown>;
    if (marks.bold) text = `<strong>${text}</strong>`;
    if (marks.italic) text = `<em>${text}</em>`;
    if (marks.underline) text = `<u>${text}</u>`;
    return text;
  }
  const element = node as unknown as { children: Descendant[]; type?: string };
  const children = element.children.map(serializeNode).join('');
  switch (element.type) {
    case 'heading-one': return `<h1>${children}</h1>`;
    case 'heading-two': return `<h2>${children}</h2>`;
    case 'heading-three': return `<h3>${children}</h3>`;
    case 'block-quote': return `<blockquote><p>${children}</p></blockquote>`;
    case 'numbered-list': return `<ol>${children}</ol>`;
    case 'bulleted-list': return `<ul>${children}</ul>`;
    case 'list-item': return `<li>${children}</li>`;
    case 'link':
      const linkNode = node as unknown as Record<string, unknown>;
      return `<a href="${escapeHtml(String(linkNode.url))}">${children}</a>`;
    default: return `<p>${children}</p>`;
  }
}

function isMarkActive(editor: Editor, format: string): boolean {
  const marks = Editor.marks(editor) as Record<string, unknown> | null;
  return marks ? Boolean(marks[format]) : false;
}

function toggleMark(editor: Editor, format: string): void {
  const isActive = isMarkActive(editor, format);
  if (isActive) Editor.removeMark(editor, format);
  else Editor.addMark(editor, format, true);
}

const LIST_TYPES = ['numbered-list', 'bulleted-list'];

function isBlockActive(editor: Editor, format: string): boolean {
  const { selection } = editor;
  if (!selection) return false;
  const [match] = Array.from(Editor.nodes(editor, {
    at: Editor.unhangRange(editor, selection),
    match: (n) => {
      if (!SlateElement.isElement(n)) return false;
      const element = n as unknown as Record<string, unknown>;
      return element.type === format;
    },
  }));
  return !!match;
}

function toggleBlock(editor: Editor, format: string): void {
  const isActive = isBlockActive(editor, format);
  const isList = LIST_TYPES.includes(format);
  Transforms.unwrapNodes(editor, {
    match: (n) => {
      if (!SlateElement.isElement(n)) return false;
      const element = n as unknown as Record<string, unknown>;
      const type = element.type as string | undefined;
      return type !== undefined && LIST_TYPES.includes(type);
    },
    split: true
  });
  Transforms.setNodes(editor, { type: isActive ? 'paragraph' : isList ? 'list-item' : format } as Partial<SlateElement>);
  if (!isActive && isList) {
    const block = { type: format as string, children: [] } as unknown as SlateElement;
    Transforms.wrapNodes(editor, block);
  }
}

/* ================================================================
   Sub-components
   ================================================================ */

function ChevronIcon({ expanded }: { expanded: boolean }) {
  return (
    <motion.svg
      animate={{ rotate: expanded ? 90 : 0 }}
      transition={{ duration: 0.2 }}
      className="w-4 h-4 text-slate-400 shrink-0"
      viewBox="0 0 20 20"
      fill="currentColor"
    >
      <path fillRule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clipRule="evenodd" />
    </motion.svg>
  );
}

function VideoUploadZone({
  videoFile,
  onFileSelect,
  onRemove,
}: {
  videoFile: File | null;
  onFileSelect: (file: File) => void;
  onRemove: () => void;
}) {
  const [isDragging, setIsDragging] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
    const file = e.dataTransfer.files[0];
    if (file) onFileSelect(file);
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) onFileSelect(file);
  };

  const handleSelect = (file: File) => {
    if (!VALID_VIDEO_TYPES.includes(file.type)) {
      toast.error('Invalid video format. Use MP4, WebM, or OGG.');
      return;
    }
    if (file.size > MAX_VIDEO_SIZE) {
      toast.error('Video must be under 500MB.');
      return;
    }
    onFileSelect(file);
  };

  return (
    <div
      onDragOver={(e) => { e.preventDefault(); setIsDragging(true); }}
      onDragLeave={() => setIsDragging(false)}
      onDrop={handleDrop}
      onClick={() => inputRef.current?.click()}
      className={`border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-all duration-200 ${
        isDragging
          ? 'border-indigo-500 bg-indigo-50/70'
          : videoFile
            ? 'border-emerald-400 bg-emerald-50/50'
            : 'border-slate-300 hover:border-indigo-400 hover:bg-slate-50'
      }`}
    >
      <input ref={inputRef} type="file" accept={VALID_VIDEO_TYPES.join(',')} className="hidden" onChange={handleChange} />
      {videoFile ? (
        <div className="flex items-center justify-center gap-3">
          <div className="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg className="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div className="text-left">
            <p className="text-sm font-medium text-slate-700">{videoFile.name}</p>
            <p className="text-xs text-slate-500">{formatFileSize(videoFile.size)}</p>
          </div>
          <button
            onClick={(e) => { e.stopPropagation(); onRemove(); }}
            className="ml-2 text-red-500 hover:text-red-700 text-xs font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors"
          >
            Remove
          </button>
        </div>
      ) : (
        <div>
          <div className="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center mx-auto mb-3">
            <svg className="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
          </div>
          <p className="text-slate-600 text-sm">Drop your video here or <span className="text-indigo-600 font-medium underline underline-offset-2">browse</span></p>
          <p className="text-xs text-slate-400 mt-1">MP4, WebM, OGG &middot; Max 500MB</p>
        </div>
      )}
    </div>
  );
}

function ToolbarButton({ active, onMouseDown, children }: { active: boolean; onMouseDown: (e: React.MouseEvent) => void; children: React.ReactNode }) {
  return (
    <button
      type="button"
      onMouseDown={onMouseDown}
      className={`w-7 h-7 rounded flex items-center justify-center text-xs font-bold transition-colors ${
        active ? 'bg-indigo-100 text-indigo-700' : 'text-slate-500 hover:bg-slate-100'
      }`}
    >
      {children}
    </button>
  );
}

function RichTextEditor({ onChange }: { onChange: (html: string) => void }) {
  const editor = useMemo(() => withReact(createEditor()), []);
  const handleChange = useCallback(
    (newValue: Descendant[]) => {
      const isAstChange = editor.operations.some((op) => op.type !== 'set_selection');
      if (isAstChange) {
        onChange(serializeSlateToHtml(newValue));
      }
    },
    [editor, onChange],
  );

  const renderElement = useCallback((props: any) => {
    const { attributes, children, element } = props;
    const elementType = (element as any).type;
    switch (elementType) {
      case 'heading-one': return <h1 {...attributes} className="text-lg font-bold text-slate-900">{children}</h1>;
      case 'heading-two': return <h2 {...attributes} className="text-base font-bold text-slate-900">{children}</h2>;
      case 'heading-three': return <h3 {...attributes} className="text-sm font-bold text-slate-900">{children}</h3>;
      case 'block-quote': return <blockquote {...attributes} className="border-l-2 border-indigo-300 pl-3 italic text-slate-600">{children}</blockquote>;
      case 'numbered-list': return <ol {...attributes} className="list-decimal pl-5">{children}</ol>;
      case 'bulleted-list': return <ul {...attributes} className="list-disc pl-5">{children}</ul>;
      case 'list-item': return <li {...attributes}>{children}</li>;
      case 'link': return <a {...attributes} href={String((element as any).url)} className="text-indigo-600 underline">{children}</a>;
      default: return <p {...attributes}>{children}</p>;
    }
  }, []);

  const renderLeaf = useCallback((props: any) => {
    const { attributes, children, leaf } = props;
    let el = <span {...attributes}>{children}</span>;
    if (leaf.bold) el = <strong {...attributes}>{children}</strong>;
    if (leaf.italic) el = <em {...attributes}>{children}</em>;
    if (leaf.underline) el = <u {...attributes}>{children}</u>;
    return el;
  }, []);

  return (
    <div className="border border-slate-300 rounded-lg overflow-hidden bg-white">
      <div className="flex items-center gap-0.5 px-2 py-1.5 border-b border-slate-200 bg-slate-50/50">
        <ToolbarButton active={isMarkActive(editor, 'bold')} onMouseDown={(e) => { e.preventDefault(); toggleMark(editor, 'bold'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.6 10.794c.124-.312.2-.724.2-1.094 0-1.74-1.41-3.2-3.2-3.2H7.6v12h5.6c1.74 0 3.2-1.46 3.2-3.2 0-1.28-.84-2.36-2-2.806zm-4.4-1.994h1.6c.44 0 .8.36.8.8s-.36.8-.8.8H11.2V8.8zm2 6.4h-2v-1.6h2c.44 0 .8.36.8.8s-.36.8-.8.8z"/></svg>
        </ToolbarButton>
        <ToolbarButton active={isMarkActive(editor, 'italic')} onMouseDown={(e) => { e.preventDefault(); toggleMark(editor, 'italic'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4v3h2.3l-3.3 10H6v3h8v-3h-2.3l3.3-10H18V4z"/></svg>
        </ToolbarButton>
        <ToolbarButton active={isMarkActive(editor, 'underline')} onMouseDown={(e) => { e.preventDefault(); toggleMark(editor, 'underline'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg>
        </ToolbarButton>
        <div className="w-px h-5 bg-slate-300 mx-1" />
        <ToolbarButton active={isBlockActive(editor, 'heading-two')} onMouseDown={(e) => { e.preventDefault(); toggleBlock(editor, 'heading-two'); }}>
          <span className="text-[10px]">H2</span>
        </ToolbarButton>
        <ToolbarButton active={isBlockActive(editor, 'heading-three')} onMouseDown={(e) => { e.preventDefault(); toggleBlock(editor, 'heading-three'); }}>
          <span className="text-[10px]">H3</span>
        </ToolbarButton>
        <div className="w-px h-5 bg-slate-300 mx-1" />
        <ToolbarButton active={isBlockActive(editor, 'bulleted-list')} onMouseDown={(e) => { e.preventDefault(); toggleBlock(editor, 'bulleted-list'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z"/></svg>
        </ToolbarButton>
        <ToolbarButton active={isBlockActive(editor, 'numbered-list')} onMouseDown={(e) => { e.preventDefault(); toggleBlock(editor, 'numbered-list'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/></svg>
        </ToolbarButton>
        <ToolbarButton active={isBlockActive(editor, 'block-quote')} onMouseDown={(e) => { e.preventDefault(); toggleBlock(editor, 'block-quote'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>
        </ToolbarButton>
      </div>
      <Slate editor={editor} initialValue={SLATE_EMPTY} onChange={handleChange}>
        <Editable
          renderElement={renderElement}
          renderLeaf={renderLeaf}
          placeholder="Write your lesson content here..."
          className="px-4 py-3 min-h-[140px] text-sm text-slate-700 focus:outline-none prose prose-slate prose-sm max-w-none"
          spellCheck
        />
      </Slate>
    </div>
  );
}

/* ================================================================
   Main Component
   ================================================================ */

export default function CourseForm({ mode, initialData, courseId, onSuccess, onCancel }: CourseFormProps) {
  const [state, dispatch] = useReducer(formReducer, initialState);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [progress, setProgress] = useState<ProgressState>({
    isSubmitting: false,
    currentStep: '',
    stepIndex: 0,
    totalSteps: 0,
    errors: [],
  });
  const [isInitialized, setIsInitialized] = useState(false);

  const { data: categories = [] } = useQuery<Category[]>({
    queryKey: ['instructor', 'course-form', 'categories'],
    queryFn: async () => {
      const response = await categoryApi.list();
      return response.data.success ? (response.data.data as Category[]) : [];
    },
  });

  // Initialize form with existing data when editing
  useEffect(() => {
    if (mode === 'edit' && initialData && !isInitialized) {
      const chaptersData = initialData.chapters?.map(ch => ({
        _id: String(ch.chapter_id),
        title: ch.title || '',
        description: ch.description || '',
        sort_order: ch.sort_order || 0,
        isExpanded: false,
        lessons: ch.lessons?.map(ls => ({
          _id: String(ls.lesson_id),
          title: ls.title || '',
          content: ls.content || '',
          sort_order: ls.sort_order || 0,
          isExpanded: false,
          videoFile: null,
          resources: [],
        })) || [],
      })) || [];

      dispatch({
        type: 'RESET_STATE',
        state: {
          title: initialData.title || '',
          description: initialData.description || '',
          price: initialData.price?.toString() || '',
          difficulty: initialData.difficulty || 'All Level',
          language: initialData.language || 'Vietnamese',
          category_ids: initialData.categories?.map(c => c.id) || [],
          objectives: initialData.objectives?.map(o => o.objective || '').filter(o => o.trim()) || [''],
          requirements: initialData.requirements?.map(r => r.requirement || '').filter(r => r.trim()) || [''],
          chapters: chaptersData,
        },
      });
      setIsInitialized(true);
    } else if (mode === 'create' && !isInitialized) {
      setIsInitialized(true);
    }
  }, [mode, initialData, isInitialized]);

  const toggleCategory = (id: string) => {
    const ids = state.category_ids.includes(id)
      ? state.category_ids.filter((i) => i !== id)
      : [...state.category_ids, id];
    dispatch({ type: 'SET_FIELD', field: 'category_ids', value: ids });
  };

  const handleSubmit = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    const validationErrors = validateForm(state);
    setErrors(validationErrors);
    if (Object.keys(validationErrors).length > 0) {
      const errorMessages = Object.values(validationErrors);
      if (errorMessages.length <= 5) {
        toast.error(`Missing required fields:\n${errorMessages.join('\n')}`);
      } else {
        toast.error(`Missing required fields:\n${errorMessages.slice(0, 5).join('\n')}\n+${errorMessages.length - 5} more`);
      }
      return;
    }

    const totalSteps = calculateTotalSteps(state);
    let stepIndex = 0;
    const advance = (label: string) => {
      stepIndex += 1;
      setProgress((prev) => ({ ...prev, currentStep: label, stepIndex }));
    };

    setProgress({
      isSubmitting: true,
      currentStep: mode === 'create' ? 'Creating course...' : 'Updating course...',
      stepIndex: 0,
      totalSteps,
      errors: []
    });

    try {
      if (mode === 'create') {
        // Create course
        advance('Creating course...');
        const courseRes = await instructorApi.createCourse({
          title: state.title,
          description: state.description,
          price: parseFloat(state.price),
          difficulty: state.difficulty,
          language: state.language,
          category_ids: state.category_ids,
          objectives: state.objectives.filter((o) => o.trim()),
          requirements: state.requirements.filter((r) => r.trim()),
        });
        const newCourseId = extractId(courseRes, 'course_id');

        // Create chapters + lessons + uploads
        for (let ci = 0; ci < state.chapters.length; ci++) {
          const ch = state.chapters[ci];
          advance(`Creating chapter: ${ch.title || `Chapter ${ci + 1}`}`);

          const chRes = await chapterApi.create(newCourseId, {
            title: ch.title,
            description: ch.description,
            sort_order: ci,
          });
          const chapterId = extractId(chRes, 'chapter_id');

          for (let li = 0; li < ch.lessons.length; li++) {
            const ls = ch.lessons[li];
            advance(`Creating lesson: ${ls.title || `Lesson ${li + 1}`}`);

            const lsRes = await courseApi.addLesson(newCourseId, chapterId, {
              title: ls.title,
              content: ls.content,
              sort_order: li,
            });
            const lessonId = extractId(lsRes, 'lesson_id');

            // Video upload
            if (ls.videoFile) {
              advance(`Uploading video for: ${ls.title}`);
              const initRes = await lessonApi.initiateVideoUpload(lessonId, {
                title: ls.videoFile.name,
                filename: ls.videoFile.name,
                mime_type: ls.videoFile.type,
                file_size_bytes: ls.videoFile.size,
                duration: 0,
                sort_order: 0,
              });
              const initData = (initRes.data.data ?? initRes.data) as Record<string, unknown>;
              const uploadMode = initData.upload_mode as string;
              const videoId = String(initData.video_id);

              if (uploadMode === 'single') {
                const etag = await uploadSingleVideoToS3(
                  ls.videoFile,
                  initData.upload as { url: string; headers?: Record<string, string> },
                  { signal: new AbortController().signal, onProgress: () => {} },
                );
                await lessonApi.completeVideoUpload(lessonId, videoId, { etag });
              } else {
                const parts = await uploadMultipartVideoToS3(
                  ls.videoFile,
                  initData.parts as { part_number: number; url: string; headers?: Record<string, string> }[],
                  initData.part_size_bytes as number,
                  { signal: new AbortController().signal, onProgress: () => {} },
                );
                await lessonApi.completeVideoUpload(lessonId, videoId, {
                  upload_id: initData.upload_id,
                  parts,
                });
              }
            }

            // Resources
            for (const res of ls.resources) {
              advance(`Adding resource: ${res.title}`);
              await lessonApi.addResource(lessonId, {
                resource_path: res.file.name,
                title: res.title,
                sort_order: res.sort_order,
              });
            }
          }
        }

        toast.success('Course created successfully!');
      } else {
        // Update existing course
        if (!courseId) throw new Error('Course ID is required for update');

        advance('Updating course details...');
        await instructorApi.updateCourse(courseId, {
          title: state.title,
          description: state.description,
          price: parseFloat(state.price),
          difficulty: state.difficulty,
          language: state.language,
          category_ids: state.category_ids,
          objectives: state.objectives.filter((o) => o.trim()),
          requirements: state.requirements.filter((r) => r.trim()),
        });

        toast.success('Course updated successfully!');
      }

      onSuccess();
    } catch (err: unknown) {
      const errorObj = err as { response?: { data?: { message?: string } } };
      const msg = errorObj.response?.data?.message || `An error occurred during course ${mode}.`;
      setProgress((prev) => ({ ...prev, errors: [msg] }));
    }
  }, [state, mode, courseId, onSuccess]);

  const closeProgress = () => {
    if (progress.errors.length > 0) {
      setProgress({ isSubmitting: false, currentStep: '', stepIndex: 0, totalSteps: 0, errors: [], courseId: progress.courseId });
    }
  };

  if (!isInitialized) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  return (
    <div className="-m-6 min-h-screen flex flex-col">
      <Toaster position="top-right" />
      {/* ======== Progress Overlay ======== */}
      <AnimatePresence>
        {progress.isSubmitting && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center"
          >
            <motion.div
              initial={{ scale: 0.9, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              className="bg-white rounded-2xl p-10 max-w-md w-full mx-4 shadow-2xl"
            >
              <h3 className="font-sora text-xl font-bold text-slate-900 mb-1">
                {mode === 'create' ? 'Creating Your Course' : 'Updating Your Course'}
              </h3>
              <p className="text-slate-500 text-sm mb-6">{progress.currentStep}</p>

              <div className="h-2 bg-slate-100 rounded-full overflow-hidden mb-3">
                <motion.div
                  className="h-full bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-full"
                  animate={{ width: `${progress.totalSteps > 0 ? (progress.stepIndex / progress.totalSteps) * 100 : 0}%` }}
                  transition={{ duration: 0.4, ease: 'easeOut' }}
                />
              </div>
              <p className="text-xs text-slate-400 mb-4">
                Step {progress.stepIndex} of {progress.totalSteps}
              </p>

              {progress.errors.length > 0 && (
                <div className="mt-2 p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-700 text-sm">
                  {progress.errors.map((err, i) => <p key={i}>{err}</p>)}
                  <div className="flex gap-3 mt-3">
                    {progress.courseId && (
                      <button
                        onClick={() => {/* Navigate to edit */}}
                        className="text-indigo-600 font-medium text-sm hover:underline"
                      >
                        Continue editing
                      </button>
                    )}
                    <button onClick={closeProgress} className="text-slate-600 font-medium text-sm hover:underline">
                      Close
                    </button>
                  </div>
                </div>
              )}
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* ======== Hero Section ======== */}
      <section className="bg-gradient-to-br from-indigo-900 via-indigo-800 to-indigo-700 px-10 py-12">
        <p className="text-indigo-300 text-sm font-medium tracking-wide uppercase mb-1">
          {mode === 'create' ? 'New Course' : 'Edit Course'}
        </p>
        <h1 className="font-sora text-3xl font-bold text-white mb-8">
          {mode === 'create' ? 'Create Your Course' : 'Edit Your Course'}
        </h1>

        <div className="max-w-3xl space-y-5">
          <div>
            <input
              type="text"
              value={state.title}
              onChange={(e) => { dispatch({ type: 'SET_FIELD', field: 'title', value: e.target.value }); if (errors.title) setErrors((prev) => { const n = { ...prev }; delete n.title; return n; }); }}
              placeholder="Course Title *"
              className={`w-full bg-white/10 backdrop-blur-sm border rounded-xl px-5 py-4 text-xl font-sora font-semibold text-white placeholder:text-indigo-300/60 focus:ring-2 focus:ring-indigo-400 focus:bg-white/[0.15] focus:outline-none transition-all ${
                errors.title ? 'border-rose-400' : 'border-white/20'
              }`}
            />
            {errors.title && <p className="mt-1.5 text-sm text-rose-300">{errors.title}</p>}
          </div>
          <div>
            <textarea
              value={state.description}
              onChange={(e) => { dispatch({ type: 'SET_FIELD', field: 'description', value: e.target.value }); if (errors.description) setErrors((prev) => { const n = { ...prev }; delete n.description; return n; }); }}
              placeholder="Describe what students will learn..."
              rows={4}
              className={`w-full bg-white/10 backdrop-blur-sm border rounded-xl px-5 py-4 text-white placeholder:text-indigo-300/60 focus:ring-2 focus:ring-indigo-400 focus:bg-white/[0.15] focus:outline-none transition-all resize-none ${
                errors.description ? 'border-rose-400' : 'border-white/20'
              }`}
            />
            {errors.description && <p className="mt-1.5 text-sm text-rose-300">{errors.description}</p>}
          </div>
        </div>
      </section>

      {/* ======== Course Meta ======== */}
      <section className="bg-white border-b border-slate-200 px-10 py-8">
        <div className="flex items-center gap-2 mb-6">
          <div className="w-1 h-6 bg-indigo-500 rounded-full" />
          <h2 className="font-sora text-lg font-semibold text-slate-900">Course Details</h2>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1.5">Price ($) *</label>
            <input
              type="number"
              value={state.price}
              onChange={(e) => { dispatch({ type: 'SET_FIELD', field: 'price', value: e.target.value }); if (errors.price) setErrors((prev) => { const n = { ...prev }; delete n.price; return n; }); }}
              min="0"
              step="0.01"
              placeholder="99.99"
              className={`w-full px-4 py-2.5 border rounded-lg text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all ${
                errors.price ? 'border-rose-400' : 'border-slate-300'
              }`}
            />
            {errors.price && <p className="mt-1 text-xs text-rose-500">{errors.price}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1.5">Difficulty</label>
            <select
              value={state.difficulty}
              onChange={(e) => dispatch({ type: 'SET_FIELD', field: 'difficulty', value: e.target.value })}
              className="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all"
            >
              <option value="All Level">All Levels</option>
              <option value="Beginner">Beginner</option>
              <option value="Intermediate">Intermediate</option>
              <option value="Expert">Expert</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1.5">Language</label>
            <select
              value={state.language}
              onChange={(e) => dispatch({ type: 'SET_FIELD', field: 'language', value: e.target.value })}
              className="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all"
            >
              <option value="Vietnamese">Vietnamese</option>
              <option value="English">English</option>
              <option value="Japanese">Japanese</option>
              <option value="Korean">Korean</option>
              <option value="Chinese">Chinese</option>
            </select>
          </div>
        </div>

        {/* Categories as toggle pills */}
        <div>
          <label className="block text-sm font-medium text-slate-700 mb-2">Categories</label>
          {categories.length > 0 ? (
            <div className="flex flex-wrap gap-2">
              {categories.map((cat) => {
                const selected = state.category_ids.includes(cat.id);
                return (
                  <button
                    key={cat.id}
                    type="button"
                    onClick={() => toggleCategory(cat.id)}
                    className={`px-3.5 py-1.5 rounded-full text-sm font-medium transition-all duration-200 ${
                      selected
                        ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                    }`}
                  >
                    {cat.name}
                  </button>
                );
              })}
            </div>
          ) : (
            <p className="text-sm text-slate-400">Loading categories...</p>
          )}
        </div>
      </section>

      {/* ======== Objectives ======== */}
      <section className="bg-slate-50 px-10 py-8">
        <div className="flex items-center gap-2 mb-6">
          <div className="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-bold">
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
          </div>
          <h2 className="font-sora text-lg font-semibold text-slate-900">Learning Objectives</h2>
        </div>
        <p className="text-sm text-slate-500 mb-4">What will students learn in this course?</p>
        <div className="space-y-3">
          <AnimatePresence initial={false}>
            {state.objectives.map((obj, i) => (
              <motion.div
                key={`obj-${i}`}
                initial={{ opacity: 0, x: -12 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -12, height: 0 }}
                transition={{ duration: 0.2 }}
                className="flex gap-2"
              >
                <span className="shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-semibold mt-2">
                  {i + 1}
                </span>
                <input
                  type="text"
                  value={obj}
                  onChange={(e) => dispatch({ type: 'UPDATE_ARRAY_ITEM', field: 'objectives', index: i, value: e.target.value })}
                  placeholder="e.g., Build real-world web applications"
                  className="flex-1 px-4 py-2.5 border border-slate-300 rounded-lg text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all"
                />
                {state.objectives.length > 1 && (
                  <button
                    type="button"
                    onClick={() => dispatch({ type: 'REMOVE_ARRAY_ITEM', field: 'objectives', index: i })}
                    className="shrink-0 px-2 text-slate-400 hover:text-rose-500 transition-colors"
                  >
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                )}
              </motion.div>
            ))}
          </AnimatePresence>
        </div>
        <button
          type="button"
          onClick={() => dispatch({ type: 'ADD_ARRAY_ITEM', field: 'objectives' })}
          className="mt-4 text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-1.5 transition-colors"
        >
          <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Add Objective
        </button>
      </section>

      {/* ======== Requirements ======== */}
      <section className="bg-white px-10 py-8">
        <div className="flex items-center gap-2 mb-6">
          <div className="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-bold">
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
          </div>
          <h2 className="font-sora text-lg font-semibold text-slate-900">Prerequisites</h2>
        </div>
        <p className="text-sm text-slate-500 mb-4">What should students know before taking this course?</p>
        <div className="space-y-3">
          <AnimatePresence initial={false}>
            {state.requirements.map((req, i) => (
              <motion.div
                key={`req-${i}`}
                initial={{ opacity: 0, x: -12 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -12, height: 0 }}
                transition={{ duration: 0.2 }}
                className="flex gap-2"
              >
                <span className="shrink-0 w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-semibold mt-2">
                  {i + 1}
                </span>
                <input
                  type="text"
                  value={req}
                  onChange={(e) => dispatch({ type: 'UPDATE_ARRAY_ITEM', field: 'requirements', index: i, value: e.target.value })}
                  placeholder="e.g., Basic understanding of HTML and CSS"
                  className="flex-1 px-4 py-2.5 border border-slate-300 rounded-lg text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all"
                />
                {state.requirements.length > 1 && (
                  <button
                    type="button"
                    onClick={() => dispatch({ type: 'REMOVE_ARRAY_ITEM', field: 'requirements', index: i })}
                    className="shrink-0 px-2 text-slate-400 hover:text-rose-500 transition-colors"
                  >
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                )}
              </motion.div>
            ))}
          </AnimatePresence>
        </div>
        <button
          type="button"
          onClick={() => dispatch({ type: 'ADD_ARRAY_ITEM', field: 'requirements' })}
          className="mt-4 text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-1.5 transition-colors"
        >
          <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Add Requirement
        </button>
      </section>

      {/* ======== Curriculum Builder ======== */}
      <section className="bg-slate-50 border-t border-slate-200 px-10 py-8 flex-1">
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-2">
            <div className="w-1 h-6 bg-indigo-500 rounded-full" />
            <h2 className="font-sora text-lg font-semibold text-slate-900">Curriculum</h2>
          </div>
          <button
            type="button"
            onClick={() => dispatch({ type: 'ADD_CHAPTER' })}
            className="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200"
          >
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Chapter
          </button>
        </div>

        {state.chapters.length === 0 ? (
          <div className="text-center py-16">
            <div className="w-16 h-16 rounded-2xl bg-slate-200/70 flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
              </svg>
            </div>
            <p className="text-slate-600 font-medium mb-1">No chapters yet</p>
            <p className="text-sm text-slate-400">Add your first chapter to start building your curriculum</p>
          </div>
        ) : (
          <div className="space-y-4">
            <AnimatePresence initial={false}>
              {state.chapters.map((chapter, ci) => (
                <motion.div
                  key={chapter._id}
                  initial={{ opacity: 0, y: 12 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, y: -12 }}
                  transition={{ duration: 0.25 }}
                  className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden"
                >
                  {/* Chapter Header */}
                  <div
                    className="flex items-center gap-3 px-5 py-4 cursor-pointer hover:bg-slate-50/50 transition-colors"
                    onClick={() => dispatch({ type: 'TOGGLE_CHAPTER', index: ci })}
                  >
                    <ChevronIcon expanded={chapter.isExpanded} />
                    <span className="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                      Ch. {ci + 1}
                    </span>
                    <input
                      type="text"
                      value={chapter.title}
                      onChange={(e) => dispatch({ type: 'UPDATE_CHAPTER', index: ci, field: 'title', value: e.target.value })}
                      onClick={(e) => e.stopPropagation()}
                      placeholder="Chapter Title"
                      className="flex-1 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:outline-none bg-transparent"
                    />
                    <span className="text-xs text-slate-400">{chapter.lessons.length} lesson{chapter.lessons.length !== 1 ? 's' : ''}</span>
                    <button
                      type="button"
                      onClick={(e) => { e.stopPropagation(); dispatch({ type: 'REMOVE_CHAPTER', index: ci }); }}
                      className="shrink-0 text-slate-400 hover:text-rose-500 transition-colors p-1 rounded hover:bg-rose-50"
                    >
                      <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                      </svg>
                    </button>
                  </div>

                  {/* Chapter description */}
                  {chapter.isExpanded && (
                    <div className="px-5 pb-3 border-b border-slate-100">
                      <input
                        type="text"
                        value={chapter.description}
                        onChange={(e) => dispatch({ type: 'UPDATE_CHAPTER', index: ci, field: 'description', value: e.target.value })}
                        placeholder="Chapter description (optional)"
                        className="w-full text-xs text-slate-600 placeholder:text-slate-400 focus:outline-none bg-transparent py-1"
                      />
                    </div>
                  )}

                  {/* Lessons */}
                  <AnimatePresence initial={false}>
                    {chapter.isExpanded && (
                      <motion.div
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: 'auto', opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.3, ease: 'easeInOut' }}
                      >
                        <div className="px-5 py-3">
                          {chapter.lessons.length > 0 ? (
                            <div className="space-y-2 mb-3">
                              {chapter.lessons.map((lesson, li) => (
                                <motion.div
                                  key={lesson._id}
                                  initial={{ opacity: 0, x: -8 }}
                                  animate={{ opacity: 1, x: 0 }}
                                  transition={{ duration: 0.2 }}
                                  className="border border-slate-200 rounded-lg overflow-hidden"
                                >
                                  {/* Lesson header */}
                                  <div
                                    className="flex items-center gap-2 px-4 py-2.5 cursor-pointer hover:bg-slate-50/50 transition-colors"
                                    onClick={() => dispatch({ type: 'TOGGLE_LESSON', chapterIndex: ci, lessonIndex: li })}
                                  >
                                    <ChevronIcon expanded={lesson.isExpanded} />
                                    <span className="text-xs text-slate-400 font-mono">{ci + 1}.{li + 1}</span>
                                    <input
                                      type="text"
                                      value={lesson.title}
                                      onChange={(e) => dispatch({ type: 'UPDATE_LESSON', chapterIndex: ci, lessonIndex: li, field: 'title', value: e.target.value })}
                                      onClick={(e) => e.stopPropagation()}
                                      placeholder="Lesson Title"
                                      className="flex-1 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none bg-transparent"
                                    />
                                    {lesson.videoFile && (
                                      <span className="text-xs text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                                        <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" /></svg>
                                        Video
                                      </span>
                                    )}
                                    {lesson.resources.length > 0 && (
                                      <span className="text-xs text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">
                                        {lesson.resources.length} file{lesson.resources.length > 1 ? 's' : ''}
                                      </span>
                                    )}
                                    <button
                                      type="button"
                                      onClick={(e) => { e.stopPropagation(); dispatch({ type: 'REMOVE_LESSON', chapterIndex: ci, lessonIndex: li }); }}
                                      className="shrink-0 text-slate-400 hover:text-rose-500 transition-colors p-0.5 rounded hover:bg-rose-50"
                                    >
                                      <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                      </svg>
                                    </button>
                                  </div>

                                  {/* Lesson expanded content */}
                                  <AnimatePresence initial={false}>
                                    {lesson.isExpanded && (
                                      <motion.div
                                        initial={{ height: 0, opacity: 0 }}
                                        animate={{ height: 'auto', opacity: 1 }}
                                        exit={{ height: 0, opacity: 0 }}
                                        transition={{ duration: 0.25, ease: 'easeInOut' }}
                                      >
                                        <div className="border-t border-slate-100 px-4 py-4 space-y-5 bg-slate-50/40">
                                          {/* Rich text content */}
                                          <div>
                                            <label className="block text-xs font-medium text-slate-600 mb-1.5">Lesson Content</label>
                                            <RichTextEditor
                                              onChange={(html) => dispatch({ type: 'UPDATE_LESSON', chapterIndex: ci, lessonIndex: li, field: 'content', value: html })}
                                            />
                                          </div>

                                          {/* Video upload */}
                                          {mode === 'create' && (
                                            <div>
                                              <label className="block text-xs font-medium text-slate-600 mb-1.5">Video</label>
                                              <VideoUploadZone
                                                videoFile={lesson.videoFile}
                                                onFileSelect={(file) => dispatch({ type: 'SET_VIDEO_FILE', chapterIndex: ci, lessonIndex: li, file })}
                                                onRemove={() => dispatch({ type: 'SET_VIDEO_FILE', chapterIndex: ci, lessonIndex: li, file: null })}
                                              />
                                            </div>
                                          )}

                                          {/* Resources */}
                                          <div>
                                            <label className="block text-xs font-medium text-slate-600 mb-1.5">Resources</label>
                                            {lesson.resources.length > 0 && (
                                              <div className="space-y-2 mb-3">
                                                {lesson.resources.map((res, ri) => (
                                                  <div key={res._id} className="flex items-center gap-2 bg-white border border-slate-200 rounded-lg px-3 py-2">
                                                    <svg className="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                                      <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                    </svg>
                                                    <span className="flex-1 text-sm text-slate-700 truncate">{res.file.name}</span>
                                                    <span className="text-xs text-slate-400">{formatFileSize(res.file.size)}</span>
                                                    <button
                                                      type="button"
                                                      onClick={() => dispatch({ type: 'REMOVE_RESOURCE', chapterIndex: ci, lessonIndex: li, resourceIndex: ri })}
                                                      className="text-slate-400 hover:text-rose-500 transition-colors"
                                                    >
                                                      <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                                        <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                      </svg>
                                                    </button>
                                                  </div>
                                                ))}
                                              </div>
                                            )}
                                            <label className="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-700 font-medium cursor-pointer transition-colors">
                                              <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                              </svg>
                                              Add Resource
                                              <input
                                                type="file"
                                                className="hidden"
                                                onChange={(e) => {
                                                  const file = e.target.files?.[0];
                                                  if (file) dispatch({ type: 'ADD_RESOURCE', chapterIndex: ci, lessonIndex: li, file });
                                                  e.target.value = '';
                                                }}
                                              />
                                            </label>
                                          </div>
                                        </div>
                                      </motion.div>
                                    )}
                                  </AnimatePresence>
                                </motion.div>
                              ))}
                            </div>
                          ) : (
                            <p className="text-sm text-slate-400 text-center py-4">No lessons yet. Add your first lesson below.</p>
                          )}

                          <button
                            type="button"
                            onClick={() => dispatch({ type: 'ADD_LESSON', chapterIndex: ci })}
                            className="w-full py-2.5 border-2 border-dashed border-slate-300 rounded-lg text-sm text-slate-500 font-medium hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/30 transition-all"
                          >
                            + Add Lesson
                          </button>
                        </div>
                      </motion.div>
                    )}
                  </AnimatePresence>
                </motion.div>
              ))}
            </AnimatePresence>
          </div>
        )}
      </section>

      {/* ======== Sticky Action Bar ======== */}
      <div className="sticky bottom-0 bg-white/90 backdrop-blur-md border-t border-slate-200 px-10 py-4 flex items-center justify-between z-10">
        <button
          type="button"
          onClick={onCancel}
          className="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-medium transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          onClick={handleSubmit}
          className="px-8 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 font-semibold transition-colors shadow-sm shadow-indigo-200"
        >
          {mode === 'create' ? 'Create Course' : 'Save Changes'}
        </button>
      </div>
    </div>
  );
}
